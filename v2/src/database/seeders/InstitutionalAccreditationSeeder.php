<?php

/**
 * ============================================================================
 * IQArchive v2 — Institutional Accreditation Seeder
 * ============================================================================
 * File: database/seeders/InstitutionalAccreditationSeeder.php
 * Responsibility: Registers institutional accreditation categories and seeds
 *                 authentic IA evidence documents from official Bicol University
 *                 accreditation dossiers into the central storage disk.
 * Architecture: Database Seeder Layer
 * Security Context: Strictly carries null college_id and null program_id.
 * ============================================================================
 */

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class InstitutionalAccreditationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Locate or create uploader (IQA Staff)
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

        // 2. Fetch or create the 4 Official Accreditation categories
        $compReportsCat = DocumentCategory::firstOrCreate(
            ['name' => 'Compliance Reports'],
            ['scope' => 'program', 'description' => 'Area-by-area institutional compliance ledger addressing AACCUP recommendations and official actions taken.']
        );

        $selfSurveyCat = DocumentCategory::firstOrCreate(
            ['name' => 'Self Survey'],
            ['scope' => 'program', 'description' => 'Institutional self-evaluation survey matrix with automated rubric indicators and mean performance ratings.']
        );

        $suppDocsCat = DocumentCategory::firstOrCreate(
            ['name' => 'Supporting Documents to Instrument'],
            ['scope' => 'program', 'description' => 'Official evidence checklists, institutional benchmarks (S/I/O/BP), and attached verification vault files.']
        );

        $portfolioCat = DocumentCategory::firstOrCreate(
            ['name' => 'Institutional Portfolio'],
            ['scope' => 'institutional', 'description' => 'Comprehensive 7-chapter university dossier, governance structure, strategic trajectory, and institutional profile.']
        );

        $diskName = config('filesystems.default', 'local');
        $disk = Storage::disk($diskName);

        // Path to authentic Bicol University IA source documents
        $docsBasePath = base_path('../IQA-docs/Accreditation');

        $sampleDocs = [
            [
                'category' => $compReportsCat,
                'title' => 'IA 2024 Compliance to the Recommendations - Area I (Governance and Management)',
                'source_file' => $docsBasePath . '/Compliance Reports/IA_2024 Compliance to the Recommendations - AREA I.pdf',
                'stored_filename' => 'ia_compliance_area_i_2024.pdf',
                'original_filename' => 'IA_2024 Compliance to the Recommendations - AREA I.pdf',
                'mime_type' => 'application/pdf',
            ],
            [
                'category' => $compReportsCat,
                'title' => 'IA 2024 Compliance to the Recommendations - Area II (Teaching and Learning)',
                'source_file' => $docsBasePath . '/Compliance Reports/IA_2024 Compliance to the Recommendations - AREA II.pdf',
                'stored_filename' => 'ia_compliance_area_ii_2024.pdf',
                'original_filename' => 'IA_2024 Compliance to the Recommendations - AREA II.pdf',
                'mime_type' => 'application/pdf',
            ],
            [
                'category' => $selfSurveyCat,
                'title' => 'IA Area I - Governance and Management Self Survey Matrix',
                'source_file' => $docsBasePath . '/Self Survey/IA_Area I - Self Survey.pdf',
                'stored_filename' => 'ia_self_survey_area_i.pdf',
                'original_filename' => 'IA_Area I - Self Survey.pdf',
                'mime_type' => 'application/pdf',
            ],
            [
                'category' => $suppDocsCat,
                'title' => 'IA 2024 Link to Supporting Documents - Area I',
                'source_file' => $docsBasePath . '/Supporting Documents/IA_2024 Link to Supporting Documents - AREA I.pdf',
                'stored_filename' => 'ia_supporting_docs_area_i.pdf',
                'original_filename' => 'IA_2024 Link to Supporting Documents - AREA I.pdf',
                'mime_type' => 'application/pdf',
            ],
            [
                'category' => $suppDocsCat,
                'title' => 'IA 2024 Link to Supporting Documents - Area II',
                'source_file' => $docsBasePath . '/Supporting Documents/IA_2024 Link to Supporting Documents - AREA II.pdf',
                'stored_filename' => 'ia_supporting_docs_area_ii.pdf',
                'original_filename' => 'IA_2024 Link to Supporting Documents - AREA II.pdf',
                'mime_type' => 'application/pdf',
            ],
        ];

        foreach ($sampleDocs as $entry) {
            $destPath = 'documents/institutional/' . $entry['stored_filename'];

            if (File::exists($entry['source_file'])) {
                $content = File::get($entry['source_file']);
                $disk->put($destPath, $content);
                $fileBytes = File::size($entry['source_file']);
                $fileHash = hash_file('sha256', $entry['source_file']);
            } else {
                $dummyPdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n185\n%%EOF\n";
                $disk->put($destPath, $dummyPdf);
                $fileBytes = strlen($dummyPdf);
                $fileHash = hash('sha256', $dummyPdf);
            }

            Document::firstOrCreate(
                [
                    'category_id' => $entry['category']->id,
                    'title' => $entry['title'],
                ],
                [
                    'college_id' => null,
                    'program_id' => null,
                    'user_id' => $uploader->id,
                    'original_filename' => $entry['original_filename'],
                    'file_path' => $destPath,
                    'file_hash' => $fileHash,
                    'file_size_bytes' => $fileBytes,
                    'mime_type' => $entry['mime_type'],
                    'status' => 'approved',
                    'visibility' => 'univ',
                ]
            );
        }
    }
}
