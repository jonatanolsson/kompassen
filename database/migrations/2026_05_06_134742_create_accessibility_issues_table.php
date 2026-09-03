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
        Schema::create('accessibility_issues', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained('accessibility_projects')->cascadeOnDelete();
            $table->foreignUlid('page_id')->nullable()->constrained('accessibility_pages')->cascadeOnDelete();
            $table->string('title');
            $table->longText('description');
            $table->enum('severity', ['critical', 'major', 'moderate', 'minor']);
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->string('component_area')->nullable();
            $table->enum('sample_scope', ['all', 'some'])->default('some');
            $table->enum('status', ['open', 'resolved', 'wont_fix'])->default('open');
            $table->string('screenshot_url')->nullable();
            $table->longText('solution_suggestions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessibility_issues');
    }
};
