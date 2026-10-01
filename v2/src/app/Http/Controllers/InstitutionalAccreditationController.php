<?php

/**
 * ============================================================================
 * IQArchive v2 — Institutional Accreditation Controller
 * ============================================================================
 * File: app/Http/Controllers/InstitutionalAccreditationController.php
 * Responsibility: Handles university-wide Institutional Accreditation (IA) workspace
 *                 orchestration across the 4 core document types: Compliance Reports,
 *                 Self Survey, Supporting Docs to the Instrument, and Portfolio.
 * Architecture: Controller Layer
 * Security Context: Multi-tenant scope is university-wide (college_id is null).
 *                   Strictly authorized to IQA Staff, Members, and Administrators.
 * ============================================================================
 */

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Scopes\CollegeScoped;
use App\Services\DocumentStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InstitutionalAccreditationController extends Controller
{
    public function __construct(
        protected DocumentStorageService $storageService
    ) {}

    /**
     * Display the Institutional Accreditation workspace or selected document type.
     *
     * Security Reasoning: Institutional Accreditation contains university-level dossiers
     * and self-survey matrices. Gated strictly to IQA staff, members, and sysadmins.
     */
    public function index(Request $request): Response
    {
        $this->authorizeIqaAccess();

        $activeCategory = $request->input('category', 'hub');
        $search = $request->input('search');

        $iaCategoryIds = DocumentCategory::whereIn('name', [
            'Compliance Reports',
            'Self Survey',
            'Supporting Documents to Instrument',
            'Institutional Portfolio',
        ])->pluck('id');

        // Query university-level documents specifically attached to Institutional Accreditation
        $docsQuery = Document::withoutGlobalScope(CollegeScoped::class)
            ->whereNull('college_id')
            ->whereNull('program_id')
            ->whereIn('category_id', $iaCategoryIds)
            ->where('visibility', 'univ')
            ->with(['uploader:id,name,email', 'category:id,name']);

        if ($search) {
            $docsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%");
            });
        }

        $documents = $docsQuery->latest('id')->take(50)->get();

        $documentTypes = [
            [
                'id' => 'supporting-docs',
                'title' => 'Supporting Documents to Instrument',
                'description' => 'Evidence checklist criteria and cloud links mapped to AACCUP Master Survey.',
                'color' => 'blue',
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
            [
                'id' => 'portfolio',
                'title' => 'Narrative Profile',
                'description' => 'Comprehensive qualitative university narratives, executive summaries, governance structure, and institutional profile.',
                'color' => 'violet',
            ],
        ];

        return Inertia::render('Documents/Institutional-Accreditation/Index', [
            'activeCategory' => $activeCategory,
            'documentTypes' => $documentTypes,
            'documents' => $documents,
            'institution' => [
                'name' => 'Bicol University',
                'acronym' => 'BU',
                'scope' => 'Institutional Quality Assurance',
                'cycle' => 'IA Cycle 2024–2028',
                'status' => 'Institutional Assessment Active',
                'areasCount' => 9,
            ],
            'filters' => [
                'search' => $search ?? '',
            ],
        ]);
    }

    /**
     * Store an institutional accreditation evidence document or link.
     */
    public function storeDocument(Request $request): RedirectResponse
    {
        $this->authorizeIqaAccess();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_type' => ['nullable', 'string'],
            'benchmark_id' => ['nullable', 'string', 'max:100'],
            'benchmark_code' => ['nullable', 'string', 'max:50'],
            'file' => ['nullable', 'required_without:external_url', 'file', 'mimes:pdf,docx,xlsx,png,jpg', 'max:51200'],
            'external_url' => ['nullable', 'required_without:file', 'url', 'max:1000'],
        ]);

        $categoryNameMap = [
            'supporting-docs' => 'Supporting Documents to Instrument',
            'self-survey' => 'Self Survey',
            'compliance-reports' => 'Compliance Reports',
            'portfolio' => 'Institutional Portfolio',
        ];

        $categoryName = $categoryNameMap[$validated['category_type'] ?? 'supporting-docs'] ?? 'Supporting Documents to Instrument';
        $category = DocumentCategory::firstOrCreate(
            ['name' => $categoryName],
            ['scope' => 'institutional']
        );

        $uploader = Auth::user();
        $file = $request->file('file');
        $externalUrl = $validated['external_url'] ?? null;

        $document = DB::transaction(function () use ($file, $category, $uploader, $validated, $externalUrl) {
            $stored = null;
            if ($file) {
                $stored = $this->storageService->storeCommonDocument($file, $category->id);
            }

            return Document::create([
                'college_id' => null,
                'program_id' => null,
                'category_id' => $category->id,
                'user_id' => $uploader->id,
                'title' => $validated['title'],
                'external_url' => $externalUrl,
                'original_filename' => $file?->getClientOriginalName() ?? ($externalUrl ? 'Google Drive Link' : 'Document'),
                'file_path' => $stored['file_path'] ?? ($externalUrl ?? ''),
                'file_hash' => $stored['file_hash'] ?? hash('sha256', ($externalUrl ?? microtime()) . $uploader->id),
                'file_size_bytes' => $stored['file_size'] ?? 0,
                'mime_type' => $stored['mime_type'] ?? ($externalUrl ? 'application/x-url' : 'application/pdf'),
                'status' => 'iqa_appr',
                'visibility' => 'univ',
            ]);
        });

        AuditLog::create([
            'user_id' => $uploader->id,
            'action' => 'DOCUMENT_CREATED',
            'target_type' => Document::class,
            'target_id' => $document->id,
            'details' => [
                'title' => $document->title,
                'category' => $category->name,
                'benchmark_code' => $validated['benchmark_code'] ?? null,
            ],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Institutional evidence '{$document->title}' linked successfully.");
    }

    /**
     * Delete an institutional accreditation evidence document.
     */
    public function destroyDocument(Document $document, Request $request): RedirectResponse
    {
        $this->authorizeIqaAccess();

        // Enforce institutional scope security guard
        if ($document->college_id !== null) {
            abort(403, 'Unauthorized to delete non-institutional document via IA workspace.');
        }

        $title = $document->title;
        $id = $document->id;
        $document->delete();

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'DOCUMENT_DELETED',
            'target_type' => Document::class,
            'target_id' => $id,
            'details' => ['title' => $title],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Institutional evidence '{$title}' removed successfully.");
    }

    /**
     * Security Gate: Ensures only authorized IQA Staff or Administrators proceed.
     */
    protected function authorizeIqaAccess(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        $isAuthorized = $user->hasRole(['iqa_staff', 'iqa_member', 'system_admin', 'super_admin'])
            || $user->can('create', Document::class);

        if (! $isAuthorized) {
            abort(403, 'Unauthorized. Access is restricted to IQA Staff and Administrators.');
        }
    }
}
