<?php

/**
 * ============================================================================
 * IQArchive v2 — Program Accreditation Service
 * ============================================================================
 * File: app/Services/ProgramAccreditationService.php
 * Responsibility: Orchestrates college resolution, program listing, AACCUP
 *                 survey instrument structures, and level-adaptive document types.
 * Architecture: Service Layer (Called by ProgramAccreditationController)
 * Security Context: Multi-tenant queries scoped by college_id where applicable;
 *                   Strictly gates IQA administrative privileges.
 * ============================================================================
 */

namespace App\Services;

use App\Models\College;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Instrument;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProgramAccreditationService
{
    public function __construct(
        protected DocumentStorageService $storageService
    ) {}

    /**
     * Retrieve all academic colleges with program counts and optional search filtering.
     */
    public function getColleges(?string $search = null): Collection
    {
        return College::query()
            ->withCount('programs')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('campus', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Retrieve degree programs for a specific college with optional search filtering.
     */
    public function getProgramsForCollege(int $collegeId, ?string $search = null): Collection
    {
        return Program::query()
            ->where('college_id', $collegeId)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->withCount('documents')
            ->orderBy('name')
            ->get();
    }

    /**
     * Resolve the active AACCUP instrument hierarchy (10 Areas, Parameters, Criteria).
     */
    public function getActiveInstrumentHierarchy(): ?Instrument
    {
        return Instrument::where('code', 'INST-AACCUP-UG')
            ->orWhere('is_active', true)
            ->with([
                'areas' => fn ($q) => $q->orderBy('area_number'),
                'areas.parameters' => fn ($q) => $q->orderBy('parameter_letter'),
                'areas.parameters.criteria' => fn ($q) => $q->orderBy('benchmark_code'),
            ])
            ->first();
    }

    /**
     * Determine the 5 allowed document types based on program accreditation level.
     *
     * Rules:
     * - All Levels: Supporting Documents, Compliance Report, Self Survey.
     * - Levels 1 & 2: Adds Program Performance Profile (PPP).
     * - Levels 3, 4, & 5: Adds Narrative Profile.
     */
    public function resolveAllowedDocumentTypes(string $level): array
    {
        $types = [
            [
                'id' => 'supporting-documents',
                'title' => 'Supporting Documents to Instrument',
                'description' => 'Evidence checklist criteria and cloud links mapped to AACCUP Master Survey.',
                'color' => 'navy',
            ],
            [
                'id' => 'self-survey',
                'title' => 'Self-Survey Documents',
                'description' => 'Internal QA self-evaluation spreadsheets, numerical rating guides, and diagnostic compliance evaluations.',
                'color' => 'orange',
            ],
            [
                'id' => 'compliance-reports',
                'title' => 'Compliance Reports',
                'description' => 'Official compliance logs, AACCUP evaluations, corrective action reports, and certificates of accreditation.',
                'color' => 'emerald',
            ],
        ];

        $normalized = strtolower(trim($level));
        $isLevel1Or2 = str_contains($normalized, 'level 1') || str_contains($normalized, 'level 2') || str_contains($normalized, 'level i') || str_contains($normalized, 'level ii');
        $isLevel3To5 = str_contains($normalized, 'level 3')
            || str_contains($normalized, 'level 4')
            || str_contains($normalized, 'level 5')
            || str_contains($normalized, 'level iii')
            || str_contains($normalized, 'level iv')
            || str_contains($normalized, 'level v');

        if ($isLevel1Or2) {
            $types[] = ['id' => 'ppp', 'title' => 'Program Performance Profile (PPP)', 'description' => 'Institutional quantitative program data, faculty profile ratios, student demographics, and laboratory assets.', 'color' => 'blue'];
        }

        if ($isLevel3To5) {
            $types[] = ['id' => 'narrative-profile', 'title' => 'Narrative Profile', 'description' => 'Comprehensive qualitative program narratives, executive summaries, and institutional advancement evidence.', 'color' => 'violet'];
        }

        return $types;
    }

    /**
     * Store a new academic degree program under a college unit.
     */
    public function storeProgram(College $college, array $data): Program
    {
        return Program::create([
            'college_id' => $college->id,
            'name' => $data['name'],
            'code' => strtoupper($data['code']),
            'current_level' => $data['current_level'] ?? 'Candidate',
        ]);
    }

    /**
     * Store and link an evidence document to a program and optional criterion.
     */
    public function storeProgramDocument(
        Program $program,
        ?UploadedFile $file,
        string $title,
        string $categoryType,
        User $uploader,
        ?int $criterionId = null,
        ?string $externalUrl = null
    ): Document {
        return DB::transaction(function () use ($program, $file, $title, $categoryType, $uploader, $criterionId, $externalUrl) {
            $catName = $categoryType === 'supporting-documents'
                ? 'Supporting Documents to Instrument'
                : ucwords(str_replace('-', ' ', $categoryType));

            $category = DocumentCategory::firstOrCreate(
                ['name' => $catName, 'scope' => 'program'],
                ['description' => "Accreditation documents for {$categoryType}"]
            );

            $stored = null;
            if ($file) {
                $stored = $this->storageService->storeEvidence($file, $program->college_id, $program->id);
            }

            $document = Document::create([
                'college_id' => $program->college_id,
                'program_id' => $program->id,
                'category_id' => $category->id,
                'criterion_id' => $criterionId,
                'user_id' => $uploader->id,
                'title' => $title,
                'external_url' => $externalUrl,
                'original_filename' => $file?->getClientOriginalName() ?? ($externalUrl ? 'Cloud Document Link' : 'Document'),
                'file_path' => $stored['file_path'] ?? ($externalUrl ?? ''),
                'file_hash' => $stored['file_hash'] ?? hash('sha256', ($externalUrl ?? microtime()) . $uploader->id),
                'file_size_bytes' => $stored['file_size'] ?? 0,
                'mime_type' => $stored['mime_type'] ?? ($externalUrl ? 'application/x-url' : 'application/pdf'),
                'status' => 'iqa_appr',
                'visibility' => 'college',
            ]);

            return $document;
        });
    }
}
