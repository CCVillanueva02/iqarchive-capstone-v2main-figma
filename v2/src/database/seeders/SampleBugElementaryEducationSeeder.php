<?php

/**
 * ============================================================================
 * IQArchive v2 — Sample BUG Elementary Education Seeder
 * ============================================================================
 * File: database/seeders/SampleBugElementaryEducationSeeder.php
 * Responsibility: Generates sample AACCUP evidence documents showcasing diverse
 *                 document types (PDF, Word, Excel, PPTX, Image, ZIP, Google Drive)
 *                 for the Bachelor of Elementary Education program at BU Gubat.
 * Architecture: Database Seeder Layer
 * Security Context: Strictly scoped by college_id and program_id.
 * ============================================================================
 */

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\College;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\InstrumentCriterion;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SampleBugElementaryEducationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Locate College (BU Gubat) and Program (BEED)
        $college = College::where('code', 'BUGC')->orWhere('name', 'like', '%GUBAT%')->first()
            ?? College::find(17);

        if (! $college) {
            $this->command->error('BU Gubat college not found.');
            return;
        }

        $program = Program::where('college_id', $college->id)
            ->where(function ($q) {
                $q->where('code', 'BUGC-BEED')
                  ->orWhere('name', 'like', '%Elementary Education%');
            })
            ->first() ?? Program::find(123);

        if (! $program) {
            $this->command->error('Bachelor of Elementary Education program not found.');
            return;
        }

        // 2. Locate or create uploader
        $uploader = User::where('college_id', $college->id)->first()
            ?? User::whereHas('roles', fn ($q) => $q->where('name', 'iqa_staff'))->first()
            ?? User::first();

        if (! $uploader) {
            $uploader = User::create([
                'name' => 'Prof. Maria Elena Santos',
                'email' => 'mesantos@bicol-u.edu.ph',
                'college_id' => $college->id,
                'status' => 'active',
            ]);
        }

        // 3. Document categories
        $supportingCat = DocumentCategory::firstOrCreate(
            ['name' => 'Supporting Documents to Instrument', 'scope' => 'program'],
            ['description' => 'Checklist criteria evidence for inputs, implementation, outcomes, and best practices.']
        );

        $selfSurveyCat = DocumentCategory::firstOrCreate(
            ['name' => 'Self Survey', 'scope' => 'program'],
            ['description' => 'Internal QA self-evaluation spreadsheets and numerical rating guides.']
        );

        $complianceCat = DocumentCategory::firstOrCreate(
            ['name' => 'Compliance Reports', 'scope' => 'program'],
            ['description' => 'Official compliance logs, AACCUP evaluations, and certificates of accreditation.']
        );

        $narrativeCat = DocumentCategory::firstOrCreate(
            ['name' => 'Narrative Profile', 'scope' => 'program'],
            ['description' => 'Comprehensive qualitative program narratives and executive summaries.']
        );

        $diskName = config('filesystems.default', 'local');
        $disk = Storage::disk($diskName);

        // Fetch criteria for linking across Area I and Area II
        $critArea1S1 = InstrumentCriterion::where('benchmark_code', 'S.1')
            ->whereHas('parameter', fn ($q) => $q->where('parameter_letter', 'A')->whereHas('area', fn ($qa) => $qa->where('area_number', 1)))
            ->first();
        $critArea1S4 = InstrumentCriterion::where('benchmark_code', 'S.4')
            ->whereHas('parameter', fn ($q) => $q->where('parameter_letter', 'A')->whereHas('area', fn ($qa) => $qa->where('area_number', 1)))
            ->first();
        $critArea1S5 = InstrumentCriterion::where('benchmark_code', 'S.5')
            ->whereHas('parameter', fn ($q) => $q->where('parameter_letter', 'A')->whereHas('area', fn ($qa) => $qa->where('area_number', 1)))
            ->first();
        $critArea1I1 = InstrumentCriterion::where('benchmark_code', 'I.1')
            ->whereHas('parameter', fn ($q) => $q->where('parameter_letter', 'A')->whereHas('area', fn ($qa) => $qa->where('area_number', 1)))
            ->first();
        $critArea1BP1 = InstrumentCriterion::where('benchmark_code', 'BP.1')
            ->whereHas('parameter', fn ($q) => $q->where('parameter_letter', 'A')->whereHas('area', fn ($qa) => $qa->where('area_number', 1)))
            ->first();
        $critArea2S1 = InstrumentCriterion::where('benchmark_code', 'S.1')
            ->whereHas('parameter', fn ($q) => $q->where('parameter_letter', 'A')->whereHas('area', fn ($qa) => $qa->where('area_number', 2)))
            ->first();

        // 4. Sample files showcasing all document types and colorful icons
        $sampleFiles = [
            // PDF (Red Icon)
            [
                'title' => 'Area I (VMGO) Institutional & Program Outcomes Alignment Matrix',
                'filename' => 'Area_I_VMGO_Alignment_Matrix.pdf',
                'mime_type' => 'application/pdf',
                'category' => $supportingCat,
                'criterion_id' => $critArea1S1?->id,
                'status' => 'iqa_appr',
                'ref' => 'BUGC-BEED-DOC-2025-001',
            ],
            // Word / Docs (Blue Icon)
            [
                'title' => 'Department Goals and Curricular Proposals Alignment Portfolio',
                'filename' => 'BEED_Department_Goals_Alignment_Portfolio.docx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'category' => $supportingCat,
                'criterion_id' => $critArea1S4?->id,
                'status' => 'dean_appr',
                'ref' => 'BUGC-BEED-DOC-2025-002',
            ],
            // Excel / Spreadsheets (Green Icon)
            [
                'title' => 'Annual Budget Allocation Matrix and Work Financial Plan AY 2025–2026',
                'filename' => 'BEED_Work_Financial_Plan_Matrix_2025.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'category' => $supportingCat,
                'criterion_id' => $critArea1S5?->id,
                'status' => 'iqa_appr',
                'ref' => 'BUGC-BEED-DOC-2025-003',
            ],
            // Presentation / PPTX (Orange Icon)
            [
                'title' => 'AACCUP Level III Accreditation Briefing & Executive Summary Deck',
                'filename' => 'BEED_AACCUP_Level3_Accreditation_Briefing.pptx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'category' => $supportingCat,
                'criterion_id' => $critArea1I1?->id,
                'status' => 'dean_appr',
                'ref' => 'BUGC-BEED-DOC-2025-004',
            ],
            // Image / PNG (Violet Icon)
            [
                'title' => 'Campus Bulletin Board VMGO Official Dissemination Exhibit Photo',
                'filename' => 'Campus_Bulletin_Board_VMGO_Posting_Exhibit.png',
                'mime_type' => 'image/png',
                'category' => $supportingCat,
                'criterion_id' => $critArea1BP1?->id,
                'status' => 'iqa_appr',
                'ref' => 'BUGC-BEED-DOC-2025-005',
            ],
            // Archive / ZIP (Amber Icon)
            [
                'title' => 'Complete Faculty Credentials and Transcripts Verification Package',
                'filename' => 'BEED_Faculty_Credentials_PRC_Licenses.zip',
                'mime_type' => 'application/zip',
                'category' => $supportingCat,
                'criterion_id' => $critArea2S1?->id,
                'status' => 'iqa_appr',
                'ref' => 'BUGC-BEED-DOC-2025-006',
            ],
            // Self Survey (Excel - Green Icon)
            [
                'title' => 'Area II to Area X Internal QA Diagnostic Rating Sheets & Benchmarks',
                'filename' => 'Internal_QA_Diagnostic_Rating_Sheet_BEED.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'category' => $selfSurveyCat,
                'criterion_id' => null,
                'status' => 'iqa_appr',
                'ref' => 'BUGC-BEED-DOC-2025-007',
            ],
            // Compliance Reports (Word - Blue Icon)
            [
                'title' => 'AACCUP Level 2 Recommendations Compliance & Monitoring Action Log',
                'filename' => 'AACCUP_Level2_Compliance_Action_Log.docx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'category' => $complianceCat,
                'criterion_id' => null,
                'status' => 'dean_appr',
                'ref' => 'BUGC-BEED-DOC-2025-008',
            ],
            // Narrative Profile (PDF - Red Icon)
            [
                'title' => 'Comprehensive Institutional Narrative Profile & Advancement Exhibit (Level 3)',
                'filename' => 'BEED_Level3_Comprehensive_Narrative_Profile.pdf',
                'mime_type' => 'application/pdf',
                'category' => $narrativeCat,
                'criterion_id' => null,
                'status' => 'draft',
                'ref' => 'BUGC-BEED-DOC-2025-009',
            ],
        ];

        foreach ($sampleFiles as $entry) {
            $content = $this->generateFileContent($entry, $program->name);
            $ext = pathinfo($entry['filename'], PATHINFO_EXTENSION);
            $hash = hash('sha256', $content);
            $storagePath = "evidence/{$college->id}/{$program->id}/{$hash}.{$ext}";

            $disk->put($storagePath, $content);
            if ($diskName === 'local') {
                Storage::disk('public')->put($storagePath, $content);
            }

            $doc = Document::updateOrCreate(
                [
                    'college_id' => $college->id,
                    'program_id' => $program->id,
                    'title' => $entry['title'],
                ],
                [
                    'category_id' => $entry['category']->id,
                    'criterion_id' => $entry['criterion_id'] ?? null,
                    'user_id' => $uploader->id,
                    'original_filename' => $entry['filename'],
                    'file_path' => $storagePath,
                    'file_hash' => $hash,
                    'file_size_bytes' => strlen($content),
                    'mime_type' => $entry['mime_type'],
                    'status' => $entry['status'],
                    'visibility' => 'college',
                ]
            );

            AuditLog::create([
                'user_id' => $uploader->id,
                'college_id' => $college->id,
                'action' => 'DOCUMENT_SEEDED',
                'target_type' => 'App\Models\Document',
                'target_id' => $doc->id,
                'details' => json_encode([
                    'program_id' => $program->id,
                    'title' => $entry['title'],
                    'mime_type' => $entry['mime_type'],
                    'status' => $entry['status'],
                ]),
                'ip_address' => '127.0.0.1',
            ]);
        }

        // 5. Seed Google Drive Links (Google Drive Multi-Color Icon)
        Document::updateOrCreate(
            [
                'college_id' => $college->id,
                'program_id' => $program->id,
                'title' => 'Secretary Certification No. 75, s. 2024 (Official BOR Drive Copy)',
            ],
            [
                'category_id' => $supportingCat->id,
                'criterion_id' => $critArea1S1?->id,
                'user_id' => $uploader->id,
                'external_url' => 'https://drive.google.com/drive/folders/1b_MbE0DhN-DrYBwsUh-oFRSbfX3WLy2j?usp=drive_link',
                'original_filename' => 'Google Drive Shared Folder',
                'file_path' => 'https://drive.google.com/drive/folders/1b_MbE0DhN-DrYBwsUh-oFRSbfX3WLy2j?usp=drive_link',
                'file_hash' => hash('sha256', 'drive-link-75'),
                'file_size_bytes' => 0,
                'mime_type' => 'application/x-url',
                'status' => 'iqa_appr',
                'visibility' => 'college',
            ]
        );

        $this->command->info("Successfully seeded documents for {$program->name} ({$college->name}).");
    }

    /**
     * Generate appropriate file content based on document type.
     */
    private function generateFileContent(array $entry, string $programName): string
    {
        $mime = $entry['mime_type'];
        if ($mime === 'application/pdf') {
            return $this->buildPdfContent($entry['title'], $programName, $entry['ref']);
        }
        if ($mime === 'image/png') {
            return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        }
        if ($mime === 'application/zip') {
            return "PK\x05\x06\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00";
        }
        return "IQArchive Evidence Mock Content\nTitle: {$entry['title']}\nRef: {$entry['ref']}\nFormat: {$mime}\n";
    }

    /**
     * Build minimal valid PDF 1.4 binary stream.
     */
    private function buildPdfContent(string $title, string $programName, string $docRef): string
    {
        $date = date('F d, Y');
        $cleanTitle = addcslashes($title, "()\\");
        $cleanProgram = addcslashes($programName, "()\\");
        $cleanRef = addcslashes($docRef, "()\\");

        $body = "BT\n"
            . "/F1 16 Tf\n"
            . "50 730 Td\n"
            . "(BICOL UNIVERSITY — GUBAT CAMPUS) Tj\n"
            . "/F1 12 Tf\n"
            . "0 -24 Td\n"
            . "({$cleanProgram}) Tj\n"
            . "/F1 10 Tf\n"
            . "0 -20 Td\n"
            . "(Document Ref: {$cleanRef} | Date: {$date}) Tj\n"
            . "/F1 13 Tf\n"
            . "0 -35 Td\n"
            . "({$cleanTitle}) Tj\n"
            . "/F1 10 Tf\n"
            . "0 -30 Td\n"
            . "(Official accreditation evidence document cataloged in IQArchive.) Tj\n"
            . "0 -16 Td\n"
            . "(Certified true copy for AACCUP survey and quality assurance compliance.) Tj\n"
            . "ET\n";

        $bodyLen = strlen($body);

        $objects = [
            1 => "<< /Type /Catalog /Pages 2 0 R >>",
            2 => "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
            3 => "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>",
            4 => "<< /Length {$bodyLen} >>\nstream\n{$body}endstream",
            5 => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
        ];

        $output = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $num => $obj) {
            $offsets[$num] = strlen($output);
            $output .= "{$num} 0 obj\n{$obj}\nendobj\n";
        }

        $xrefStart = strlen($output);
        $output .= "xref\n0 " . (count($objects) + 1) . "\n";
        $output .= "0000000000 65535 f \n";

        foreach ($offsets as $num => $offset) {
            $output .= sprintf("%010d 00000 n \n", $offset);
        }

        $output .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $output .= "startxref\n{$xrefStart}\n%%EOF";

        return $output;
    }
}
