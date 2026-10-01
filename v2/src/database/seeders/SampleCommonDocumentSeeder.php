<?php

/**
 * ============================================================================
 * IQArchive v2 — Sample Common Documents Seeder
 * ============================================================================
 * File: database/seeders/SampleCommonDocumentSeeder.php
 * Responsibility: Generates sample institutional common documents showcasing
 *                 diverse document types (PDF, Word, Excel, PPTX, Image)
 *                 for HRDO and University Registrar offices.
 * Architecture: Database Seeder Layer
 * Security Context: Strictly carries null college_id and institutional category_id.
 * ============================================================================
 */

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SampleCommonDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Locate or create uploader (IQA Staff or first user)
        $uploader = User::whereHas('roles', fn ($q) => $q->where('name', 'iqa_staff'))->first()
            ?? User::where('email', 'iqastaff@example.com')->first()
            ?? User::first();

        if (! $uploader) {
            $uploader = User::create([
                'name' => 'IQA Staff Administrator',
                'email' => 'iqastaff@bicol-u.edu.ph',
                'status' => 'active',
            ]);
        }

        // 2. Fetch offices
        $hrdo = DocumentCategory::firstOrCreate(
            ['name' => 'HRDO', 'scope' => 'institutional'],
            ['description' => 'Human Resource Development Office: Plantilla, faculty rankings, appointment guidelines, and personnel welfare policies.']
        );

        $registrar = DocumentCategory::firstOrCreate(
            ['name' => 'University Registrar', 'scope' => 'institutional'],
            ['description' => 'Office of the University Registrar: Academic calendars, admission policies, grading circulars, and graduation statistics.']
        );

        $sampleEntries = [
            // HRDO Sample Documents
            [
                'office' => $hrdo,
                'title' => 'BU Faculty Merit Promotion Plan & Plantilla Guidelines (2025 Revised Edition)',
                'original_filename' => 'BU_HRDO_Faculty_Merit_Promotion_2025.pdf',
                'mime_type' => 'application/pdf',
                'doc_number' => 'BU-HRDO-POL-2025-001',
                'status' => 'iqa_appr',
            ],
            [
                'office' => $hrdo,
                'title' => 'Faculty Staffing & Plantilla Position Distribution Matrix 2025',
                'original_filename' => 'BU_HRDO_Faculty_Plantilla_Matrix_2025.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'doc_number' => 'BU-HRDO-DAT-2025-002',
                'status' => 'iqa_appr',
            ],
            [
                'office' => $hrdo,
                'title' => 'University Code of Conduct and Ethical Standards for Academic Personnel',
                'original_filename' => 'BU_HRDO_Code_of_Conduct_Policy_Revision.docx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'doc_number' => 'BU-HRDO-POL-2024-008',
                'status' => 'draft',
            ],
            [
                'office' => $hrdo,
                'title' => 'Faculty Professional Development & Scholarship Orientation Deck',
                'original_filename' => 'BU_HRDO_Faculty_Development_Briefing.pptx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'doc_number' => 'BU-HRDO-PRE-2025-003',
                'status' => 'iqa_appr',
            ],

            // University Registrar Sample Documents
            [
                'office' => $registrar,
                'title' => 'Official Academic Calendar & Enrollment Policies AY 2026-2027',
                'original_filename' => 'OUR_Academic_Calendar_AY2026_2027.pdf',
                'mime_type' => 'application/pdf',
                'doc_number' => 'BU-OUR-CIR-2026-004',
                'status' => 'iqa_appr',
            ],
            [
                'office' => $registrar,
                'title' => 'University Retention, Grading System & Scholastic Delinquency Manual',
                'original_filename' => 'OUR_Retention_and_Grading_Manual.docx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'doc_number' => 'BU-OUR-MAN-2025-002',
                'status' => 'dean_appr',
            ],
            [
                'office' => $registrar,
                'title' => 'University Graduation Statistics & Completion Analytics AY 2024–2025',
                'original_filename' => 'OUR_Graduation_Statistics_Analytics_2025.xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'doc_number' => 'BU-OUR-DAT-2025-005',
                'status' => 'iqa_appr',
            ],
            [
                'office' => $registrar,
                'title' => 'Freshmen Student Onboarding & Orientation Ceremonial Exhibit',
                'original_filename' => 'OUR_Student_Orientation_Ceremony_Exhibit.png',
                'mime_type' => 'image/png',
                'doc_number' => 'BU-OUR-IMG-2025-006',
                'status' => 'iqa_appr',
            ],
        ];

        $disk = config('filesystems.default', 'local');

        foreach ($sampleEntries as $entry) {
            $office = $entry['office'];
            $mime = $entry['mime_type'];
            $content = $this->generateContent($entry, $office->name);
            $ext = pathinfo($entry['original_filename'], PATHINFO_EXTENSION);
            $fileHash = hash('sha256', $content);
            $fileSize = strlen($content);
            $filePath = "evidence/common/{$office->id}/{$fileHash}.{$ext}";

            // Store physical file on storage disk
            Storage::disk($disk)->put($filePath, $content);
            if ($disk === 'local') {
                Storage::disk('public')->put($filePath, $content);
            }

            // Create or update Document record
            $document = Document::withoutGlobalScopes()->updateOrCreate(
                [
                    'category_id' => $office->id,
                    'title' => $entry['title'],
                ],
                [
                    'college_id' => null,
                    'program_id' => null,
                    'user_id' => $uploader->id,
                    'original_filename' => $entry['original_filename'],
                    'file_path' => $filePath,
                    'file_hash' => $fileHash,
                    'file_size_bytes' => $fileSize,
                    'mime_type' => $mime,
                    'status' => $entry['status'],
                    'visibility' => 'univ',
                ]
            );

            // Audit log entry
            AuditLog::firstOrCreate(
                [
                    'action' => 'document.upload.common',
                    'target_type' => Document::class,
                    'target_id' => (string) $document->id,
                ],
                [
                    'college_id' => null,
                    'user_id' => $uploader->id,
                    'ip_address' => '127.0.0.1',
                    'details' => [
                        'title' => $document->title,
                        'category_id' => $document->category_id,
                        'mime_type' => $mime,
                        'file_hash' => $document->file_hash,
                        'seeded' => true,
                    ],
                ]
            );
        }
    }

    /**
     * Generate content per file type.
     */
    private function generateContent(array $entry, string $officeName): string
    {
        $mime = $entry['mime_type'];
        if ($mime === 'application/pdf') {
            return $this->buildPdfContent($entry['title'], $officeName, $entry['doc_number']);
        }
        if ($mime === 'image/png') {
            return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        }
        return "IQArchive Common Document Mock Content\nTitle: {$entry['title']}\nRef: {$entry['doc_number']}\nOffice: {$officeName}\n";
    }

    /**
     * Build valid minimal PDF 1.4 binary content with accurate xref table.
     */
    private function buildPdfContent(string $title, string $officeName, string $docNumber): string
    {
        $date = date('F d, Y');
        $cleanTitle = addcslashes($title, "()\\");
        $cleanOffice = addcslashes($officeName, "()\\");
        $cleanDocNum = addcslashes($docNumber, "()\\");

        $body = "BT\n"
            . "/F1 16 Tf\n"
            . "50 730 Td\n"
            . "(BICOL UNIVERSITY — OFFICIAL INSTITUTIONAL ARCHIVE) Tj\n"
            . "/F1 12 Tf\n"
            . "0 -24 Td\n"
            . "({$cleanOffice}) Tj\n"
            . "/F1 10 Tf\n"
            . "0 -20 Td\n"
            . "(Document Control: {$cleanDocNum} | Date: {$date}) Tj\n"
            . "/F1 13 Tf\n"
            . "0 -35 Td\n"
            . "({$cleanTitle}) Tj\n"
            . "/F1 10 Tf\n"
            . "0 -30 Td\n"
            . "(Common reference document cataloged for university-wide AACCUP accreditation.) Tj\n"
            . "0 -16 Td\n"
            . "(Official digital copy archived in IQArchive. Uncontrolled when printed.) Tj\n"
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
