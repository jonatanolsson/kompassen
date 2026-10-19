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
        Schema::table('accessibility_projects', function (Blueprint $table) {
            $table->text('audit_scope')->nullable()->after('description');
            $table->text('scope_limitations')->nullable()->after('audit_scope');
        });

        Schema::table('accessibility_pages', function (Blueprint $table) {
            $table->text('scope_exclusion_reason')->nullable()->after('scope');
        });

        Schema::table('accessibility_reports', function (Blueprint $table) {
            $table->text('scope_notes')->nullable()->after('scope');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accessibility_reports', function (Blueprint $table) {
            $table->dropColumn('scope_notes');
        });

        Schema::table('accessibility_pages', function (Blueprint $table) {
            $table->dropColumn('scope_exclusion_reason');
        });

        Schema::table('accessibility_projects', function (Blueprint $table) {
            $table->dropColumn(['audit_scope', 'scope_limitations']);
        });
    }
};
