<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accessibility_reports', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreignId('created_by')->nullable()->change();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->ulid('series_id')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->foreignUlid('previous_version_id')->nullable()->constrained('accessibility_reports')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('audit_date_snapshot')->nullable();
            $table->string('client_logo_snapshot')->nullable();
            $table->json('report_options')->nullable();
            $table->json('document_metadata')->nullable();
            $table->longText('html_content')->nullable()->change();
            $table->unique(['series_id', 'version']);
            $table->index(['project_id', 'created_at']);
        });

        Schema::table('accessibility_projects', function (Blueprint $table) {
            $table->json('report_options')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accessibility_projects', function (Blueprint $table) {
            $table->dropColumn('report_options');
        });

        Schema::table('accessibility_reports', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreignId('created_by')->nullable(false)->change();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->dropUnique(['series_id', 'version']);
            $table->dropIndex(['project_id', 'created_at']);
            $table->dropConstrainedForeignId('previous_version_id');
            $table->dropColumn(['series_id', 'version', 'generated_at', 'audit_date_snapshot', 'client_logo_snapshot', 'report_options', 'document_metadata']);
            $table->string('html_content')->nullable()->change();
        });
    }
};
