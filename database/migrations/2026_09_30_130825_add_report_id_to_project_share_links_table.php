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
        Schema::table('project_share_links', function (Blueprint $table) {
            Schema::table('project_share_links', function (Blueprint $table) {
                $table->foreignUlid('report_id')->nullable()->constrained('accessibility_reports')->nullOnDelete();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_share_links', function (Blueprint $table) {
            Schema::table('project_share_links', function (Blueprint $table) {
                $table->dropConstrainedForeignId('report_id');
            });
        });
    }
};
