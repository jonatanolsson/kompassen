<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accessibility_projects', function (Blueprint $table): void {
            $table->enum('target_wcag_version', ['2.0', '2.1', '2.2'])->default('2.1')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('accessibility_projects')->where('target_wcag_version', '2.2')->exists()) {
            throw new RuntimeException('Cannot remove WCAG 2.2 target support while projects still use it.');
        }

        Schema::table('accessibility_projects', function (Blueprint $table): void {
            $table->enum('target_wcag_version', ['2.0', '2.1'])->default('2.1')->change();
        });
    }
};
