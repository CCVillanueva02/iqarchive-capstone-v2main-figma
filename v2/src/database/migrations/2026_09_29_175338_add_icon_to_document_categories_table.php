<?php

/**
 * ============================================================================
 * IQArchive v2 — Add icon column to document_categories
 * ============================================================================
 * Stores a Lucide icon key (e.g. 'users', 'landmark') for office sidebar display.
 * Nullable — falls back to 'building-2' in the frontend when null.
 * ============================================================================
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_categories', function (Blueprint $table) {
            $table->string('icon', 50)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('document_categories', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
