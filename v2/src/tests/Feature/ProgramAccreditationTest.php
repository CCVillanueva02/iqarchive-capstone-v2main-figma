<?php

/**
 * ============================================================================
 * IQArchive v2 — Program Accreditation Feature Tests
 * ============================================================================
 * File: tests/Feature/ProgramAccreditationTest.php
 * Purpose: Verifies Program Accreditation 3-tier workspace navigation,
 *          IQA role boundaries, level-adaptive document types, and program registration.
 * Security Context: Server-side authorization gates, IQA role checks,
 *                   and multi-tenant college scoping.
 * ============================================================================
 */

namespace Tests\Feature;

use App\Models\College;
use App\Models\DocumentCategory;
use App\Models\Instrument;
use App\Models\InstrumentArea;
use App\Models\InstrumentCriterion;
use App\Models\InstrumentParameter;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProgramAccreditationTest extends TestCase
{
    use RefreshDatabase;

    private User $iqaUser;
    private User $unauthorizedUser;
    private College $college;
    private Program $programLevel1;
    private Program $programLevel3;

    protected function setUp(): void
    {
        parent::setUp();

        $iqaRole = Role::create(['name' => 'iqa_staff', 'display_name' => 'IQA Staff']);
        $facultyRole = Role::create(['name' => 'faculty', 'display_name' => 'Faculty Member']);

        $this->college = College::create([
            'name' => 'BU GUBAT',
            'code' => 'BUGC',
            'campus' => 'BU GUBAT',
            'logo_image' => 'BUGC.png',
        ]);

        $this->programLevel1 = Program::create([
            'college_id' => $this->college->id,
            'name' => 'Bachelor of Science in Business Administration',
            'code' => 'BUGC-BSBA',
            'current_level' => 'Level 1',
        ]);

        $this->programLevel3 = Program::create([
            'college_id' => $this->college->id,
            'name' => 'Bachelor of Elementary Education',
            'code' => 'BUGC-BEED',
            'current_level' => 'Level 3',
        ]);

        $this->iqaUser = User::create([
            'name' => 'IQA Staff Member',
            'email' => 'iqa.staff@bicol-u.edu.ph',
            'status' => 'active',
        ]);
        $this->iqaUser->roles()->attach($iqaRole->id);

        $this->unauthorizedUser = User::create([
            'name' => 'Regular Faculty',
            'email' => 'faculty@bicol-u.edu.ph',
            'status' => 'active',
            'college_id' => $this->college->id,
        ]);
        $this->unauthorizedUser->roles()->attach($facultyRole->id);

        // Seed Master Instrument
        $inst = Instrument::create([
            'name' => 'AACCUP Undergraduate Master Survey Instrument',
            'code' => 'INST-AACCUP-UG',
            'type' => 'ug',
            'version' => '2026.1',
            'is_active' => true,
        ]);
        $area = InstrumentArea::create([
            'instrument_id' => $inst->id,
            'area_number' => 1,
            'name' => 'Vision, Mission, Goals, and Objectives',
        ]);
        $param = InstrumentParameter::create([
            'instrument_area_id' => $area->id,
            'parameter_letter' => 'A',
            'name' => 'Statement of VMGO',
        ]);
        InstrumentCriterion::create([
            'instrument_parameter_id' => $param->id,
            'benchmark_code' => 'S.1',
            'title' => 'The institution has a system of determining its Vision and Mission.',
            'type' => 'system',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/documents/program-accreditation');
        $response->assertRedirect('/login');
    }

    public function test_unauthorized_user_receives_403_forbidden(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)
            ->get('/documents/program-accreditation');

        $response->assertStatus(403);
    }

    public function test_iqa_user_can_access_tier_1_college_grid(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get('/documents/program-accreditation');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Documents/Program-Accreditation/Index')
                ->where('currentTier', 1)
                ->has('colleges', 1)
                ->where('colleges.0.code', 'BUGC')
                ->where('colleges.0.programs_count', 2)
            );
    }

    public function test_iqa_user_can_access_tier_2_programs_grid_for_college(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get("/documents/program-accreditation?college_id={$this->college->id}");

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Documents/Program-Accreditation/Index')
                ->where('currentTier', 2)
                ->where('selectedCollege.id', $this->college->id)
                ->has('programs', 2)
            );
    }

    public function test_level_1_program_resolves_ppp_document_type(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get("/documents/program-accreditation?college_id={$this->college->id}&program_id={$this->programLevel1->id}");

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Documents/Program-Accreditation/Index')
                ->where('currentTier', 3)
                ->where('selectedProgram.id', $this->programLevel1->id)
                ->has('documentTypes', 4) // Supporting docs, self survey, compliance, PPP
                ->where('documentTypes.3.id', 'ppp')
            );
    }

    public function test_level_3_program_resolves_narrative_profile_document_type(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get("/documents/program-accreditation?college_id={$this->college->id}&program_id={$this->programLevel3->id}");

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Documents/Program-Accreditation/Index')
                ->where('currentTier', 3)
                ->where('selectedProgram.id', $this->programLevel3->id)
                ->has('documentTypes', 4) // Supporting docs, self survey, compliance, Narrative Profile (PPP is only for levels 1-2)
                ->where('documentTypes.3.id', 'narrative-profile')
            );
    }

    public function test_tier_3_resolves_correct_document_types_for_level_5_program(): void
    {
        $programLevel5 = Program::create([
            'college_id' => $this->college->id,
            'name' => 'Doctor of Philosophy in Education',
            'code' => 'BUGC-PHD',
            'current_level' => 'Level 5',
        ]);

        $response = $this->actingAs($this->iqaUser)
            ->get("/documents/program-accreditation?college_id={$this->college->id}&program_id={$programLevel5->id}");

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Documents/Program-Accreditation/Index')
                ->where('currentTier', 3)
                ->where('selectedProgram.id', $programLevel5->id)
                ->has('documentTypes', 4)
                ->where('documentTypes.3.id', 'narrative-profile')
            );
    }

    public function test_iqa_user_can_register_new_degree_program(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->post('/documents/program-accreditation/programs', [
                'college_id' => $this->college->id,
                'name' => 'Bachelor in Agricultural Technology',
                'code' => 'BUGC-BAT',
                'current_level' => 'Candidate',
            ]);

        $response->assertRedirect("/documents/program-accreditation?college_id={$this->college->id}");
        $this->assertDatabaseHas('programs', [
            'college_id' => $this->college->id,
            'code' => 'BUGC-BAT',
            'name' => 'Bachelor in Agricultural Technology',
        ]);
    }

    public function test_tab_program_accreditation_redirects_to_program_accreditation_route(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get('/documents?tab=program-accreditation');

        $response->assertRedirect(route('documents.program-accreditation'));
    }

    public function test_supporting_documents_category_is_named_supporting_documents_to_ia_instruments(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get("/documents/program-accreditation?college_id={$this->college->id}&program_id={$this->programLevel1->id}");

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Documents/Program-Accreditation/Index')
                ->where('documentTypes.0.id', 'supporting-documents')
                ->where('documentTypes.0.title', 'Supporting Documents to Instrument')
            );
    }

    public function test_iqa_user_can_upload_document_linked_to_criterion(): void
    {
        Storage::fake(config('filesystems.default', 'local'));

        $criterion = \App\Models\InstrumentCriterion::first();
        $this->assertNotNull($criterion);

        $fakePdf = \Illuminate\Http\UploadedFile::fake()->create('VMGO_Secretary_Cert_75.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->iqaUser)
            ->post('/documents/program-accreditation/documents', [
                'program_id' => $this->programLevel1->id,
                'title' => 'Secretary Certification No. 75, s. 2024',
                'category_type' => 'supporting-documents',
                'criterion_id' => $criterion->id,
                'file' => $fakePdf,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'program_id' => $this->programLevel1->id,
            'criterion_id' => $criterion->id,
            'title' => 'Secretary Certification No. 75, s. 2024',
        ]);
    }

    public function test_iqa_user_can_attach_external_cloud_link_to_criterion(): void
    {
        $criterion = \App\Models\InstrumentCriterion::first();
        $this->assertNotNull($criterion);

        $driveUrl = 'https://drive.google.com/drive/folders/1b_MbE0DhN-DrYBwsUh-oFRSbfX3WLy2j?usp=drive_link';

        $response = $this->actingAs($this->iqaUser)
            ->post('/documents/program-accreditation/documents', [
                'program_id' => $this->programLevel1->id,
                'title' => 'BOR Resolutions Google Drive Evidence Folder',
                'category_type' => 'supporting-documents',
                'criterion_id' => $criterion->id,
                'external_url' => $driveUrl,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'program_id' => $this->programLevel1->id,
            'criterion_id' => $criterion->id,
            'title' => 'BOR Resolutions Google Drive Evidence Folder',
            'external_url' => $driveUrl,
        ]);
    }

    public function test_iqa_user_can_delete_attached_evidence(): void
    {
        $category = DocumentCategory::firstOrCreate(
            ['name' => 'Supporting Documents to Instrument', 'scope' => 'program'],
            ['description' => 'Test category']
        );

        $doc = \App\Models\Document::create([
            'college_id' => $this->college->id,
            'program_id' => $this->programLevel1->id,
            'category_id' => $category->id,
            'user_id' => $this->iqaUser->id,
            'title' => 'Temporary Obsolete Document',
            'original_filename' => 'obsolete.pdf',
            'file_path' => 'evidence/test.pdf',
            'file_hash' => hash('sha256', 'obsolete'),
            'file_size_bytes' => 100,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($this->iqaUser)
            ->delete("/documents/program-accreditation/documents/{$doc->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
    }

    public function test_iqa_user_can_access_self_survey_workspace(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get("/documents/program-accreditation?college_id={$this->college->id}&program_id={$this->programLevel1->id}&category=self-survey");

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Documents/Program-Accreditation/Index')
                ->where('currentTier', 3)
                ->where('activeCategory', 'self-survey')
                ->has('instrument.areas')
            );
    }
}
