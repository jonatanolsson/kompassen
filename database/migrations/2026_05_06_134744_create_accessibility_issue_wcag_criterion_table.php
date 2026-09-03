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
        Schema::create('accessibility_issue_wcag_criterion', function (Blueprint $table) {
            $table->ulid('accessibility_issue_id');
            $table->ulid('wcag_success_criterion_id');
            $table->foreign('accessibility_issue_id', 'fk_issue')
                ->references('id')
                ->on('accessibility_issues')
                ->cascadeOnDelete();
            $table->foreign('wcag_success_criterion_id', 'fk_criterion')
                ->references('id')
                ->on('wcag_success_criteria')
                ->cascadeOnDelete();
            $table->primary(['accessibility_issue_id', 'wcag_success_criterion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessibility_issue_wcag_criterion');
    }
};
