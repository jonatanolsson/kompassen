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
        Schema::create('accessibility_reports', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained('accessibility_projects')->cascadeOnDelete();
            $table->string('title');
            $table->text('scope')->nullable();
            $table->enum('target_wcag_level', ['A', 'AA', 'AAA'])->default('AA');
            $table->integer('total_issues')->default(0);
            $table->integer('critical_count')->default(0);
            $table->integer('major_count')->default(0);
            $table->integer('moderate_count')->default(0);
            $table->integer('minor_count')->default(0);
            $table->string('html_content')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessibility_reports');
    }
};
