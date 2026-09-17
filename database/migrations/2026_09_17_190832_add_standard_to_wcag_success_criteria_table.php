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
        Schema::table('wcag_success_criteria', function (Blueprint $table): void {
            $table->string('standard')->default('WCAG 2.1')->after('number');
            $table->string('level')->default('A')->change();
            $table->dropUnique('wcag_success_criteria_number_unique');
            $table->unique(['standard', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wcag_success_criteria', function (Blueprint $table): void {
            $table->dropUnique('wcag_success_criteria_standard_number_unique');
            $table->enum('level', ['A', 'AA', 'AAA'])->default('A')->change();
            $table->dropColumn('standard');
            $table->unique('number');
        });
    }
};
