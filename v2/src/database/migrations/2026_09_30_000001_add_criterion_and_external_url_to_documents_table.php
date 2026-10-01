<?php

/**
 * ============================================================================
 * IQArchive v2 — Add Criterion ID and External URL to Documents Table
 * ============================================================================
 * File: database/migrations/2026_09_30_000001_add_criterion_and_external_url_to_documents_table.php
 * Schema Zone: Zone 4 (Accreditation Evidence Documents)
 * Purpose: Allows evidence documents to link directly to survey instrument criteria
 *          and support external cloud URLs (e.g. Google Drive/SharePoint folders)
 *          alongside native file vault uploads.
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('criterion_id')
                ->nullable()
                ->after('category_id')
                ->constrained('instrument_criteria')
                ->nullOnDelete();

            $table->string('external_url', 1000)
                ->nullable()
                ->after('title');

            $table->string('original_filename', 255)->nullable()->change();
            $table->string('file_path', 500)->nullable()->change();
            $table->string('file_hash', 64)->nullable()->change();
            $table->unsignedBigInteger('file_size_bytes')->nullable()->change();
            $table->string('mime_type', 100)->nullable()->change();
        });

        Schema::table('instrument_criteria', function (Blueprint $table) {
            $table->json('suggested_evidence')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('instrument_criteria', function (Blueprint $table) {
            $table->dropColumn('suggested_evidence');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['criterion_id']);
            $table->dropColumn(['criterion_id', 'external_url']);
            $table->string('original_filename', 255)->nullable(false)->change();
            $table->string('file_path', 500)->nullable(false)->change();
            $table->string('file_hash', 64)->nullable(false)->change();
            $table->unsignedBigInteger('file_size_bytes')->nullable(false)->change();
            $table->string('mime_type', 100)->nullable(false)->change();
        });
    }
};
