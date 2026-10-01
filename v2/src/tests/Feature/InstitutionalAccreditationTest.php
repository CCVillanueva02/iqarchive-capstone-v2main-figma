<?php

/**
 * ============================================================================
 * IQArchive v2 — Institutional Accreditation Feature Tests
 * ============================================================================
 * File: tests/Feature/InstitutionalAccreditationTest.php
 * Purpose: Verifies Institutional Accreditation workspace access, IQA security
 *          gates, tab redirects, and Inertia prop delivery.
 * Security Context: Server-side authorization gates, IQA role boundaries,
 *                   and university-wide document scoping.
 * ============================================================================
 */

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InstitutionalAccreditationTest extends TestCase
{
    use RefreshDatabase;

    private User $iqaUser;
    private User $unauthorizedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $iqaRole = Role::create(['name' => 'iqa_staff', 'display_name' => 'IQA Staff']);
        $facultyRole = Role::create(['name' => 'faculty', 'display_name' => 'Faculty Member']);

        $this->iqaUser = User::create([
            'name' => 'IQA Staff Member',
            'email' => 'iqa.staff@bicol-u.edu.ph',
            'status' => 'active',
        ]);
        $this->iqaUser->roles()->attach($iqaRole->id);

        $this->unauthorizedUser = User::create([
            'name' => 'Faculty User',
            'email' => 'faculty@bicol-u.edu.ph',
            'status' => 'active',
        ]);
        $this->unauthorizedUser->roles()->attach($facultyRole->id);
    }

    /**
     * Test unauthenticated users are redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/documents/institutional-accreditation');
        $response->assertRedirect('/login');
    }

    /**
     * Test unauthorized users receive 403 Forbidden.
     */
    public function test_unauthorized_user_is_forbidden(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)
            ->get('/documents/institutional-accreditation');

        $response->assertStatus(403);
    }

    /**
     * Test authorized IQA staff can access the Institutional Accreditation Hub.
     */
    public function test_iqa_staff_can_view_institutional_accreditation_hub(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get('/documents/institutional-accreditation');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Institutional-Accreditation/Index')
            ->where('activeCategory', 'hub')
            ->has('documentTypes', 4)
            ->has('institution')
            ->where('institution.name', 'Bicol University')
            ->where('institution.areasCount', 9)
        );
    }

    /**
     * Test legacy tab parameter redirects to institutional-accreditation route.
     */
    public function test_legacy_institutional_tab_redirects_to_workspace(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get('/documents?tab=institutional-records');

        $response->assertRedirect(route('documents.institutional-accreditation'));
    }

    /**
     * Test category navigation passes category to view.
     */
    public function test_category_navigation_loads_correct_category_prop(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get('/documents/institutional-accreditation?category=supporting-docs');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Institutional-Accreditation/Index')
            ->where('activeCategory', 'supporting-docs')
            ->has('documents')
        );
    }

    /**
     * Test navigating to self-survey loads self-survey activeCategory.
     */
    public function test_category_navigation_loads_self_survey_category_prop(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get('/documents/institutional-accreditation?category=self-survey');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Institutional-Accreditation/Index')
            ->where('activeCategory', 'self-survey')
            ->has('documents')
        );
    }

    /**
     * Test navigating to compliance-reports loads compliance-reports activeCategory.
     */
    public function test_category_navigation_loads_compliance_reports_category_prop(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->get('/documents/institutional-accreditation?category=compliance-reports');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Documents/Institutional-Accreditation/Index')
            ->where('activeCategory', 'compliance-reports')
            ->has('documents')
        );
    }

    /**
     * Test IQA staff can store institutional evidence with cloud link.
     */
    public function test_iqa_staff_can_store_institutional_evidence_link(): void
    {
        $response = $this->actingAs($this->iqaUser)
            ->post('/documents/institutional-accreditation/documents', [
                'title' => 'BOR Resolution No. 42 Strategic Plan 2026',
                'category_type' => 'supporting-docs',
                'benchmark_code' => 'S.1',
                'external_url' => 'https://drive.google.com/file/d/test12345/view',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'title' => 'BOR Resolution No. 42 Strategic Plan 2026',
            'college_id' => null,
            'program_id' => null,
            'external_url' => 'https://drive.google.com/file/d/test12345/view',
            'visibility' => 'univ',
        ]);
    }

    /**
     * Test IQA staff can delete institutional evidence.
     */
    public function test_iqa_staff_can_delete_institutional_evidence(): void
    {
        $category = \App\Models\DocumentCategory::create([
            'name' => 'Supporting Documents to Instrument',
            'scope' => 'institutional',
        ]);

        $doc = \App\Models\Document::create([
            'college_id' => null,
            'program_id' => null,
            'category_id' => $category->id,
            'user_id' => $this->iqaUser->id,
            'title' => 'Temporary Evidence to Delete',
            'external_url' => 'https://drive.google.com/file/d/temp/view',
            'original_filename' => 'temp.url',
            'file_path' => 'https://drive.google.com/file/d/temp/view',
            'file_hash' => 'hash123',
            'file_size_bytes' => 0,
            'mime_type' => 'application/x-url',
            'status' => 'iqa_appr',
            'visibility' => 'univ',
        ]);

        $response = $this->actingAs($this->iqaUser)
            ->delete("/documents/institutional-accreditation/documents/{$doc->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
    }
}
