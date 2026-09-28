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
        Schema::create('accessibility_issue_example', function (Blueprint $table) {
            $table->ulid('accessibility_issue_id');
            $table->ulid('wcag_criterion_example_id');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('accessibility_issue_id', 'fk_issue_example_issue')
                ->references('id')
                ->on('accessibility_issues')
                ->cascadeOnDelete();
            $table->foreign('wcag_criterion_example_id', 'fk_issue_example_example')
                ->references('id')
                ->on('wcag_criterion_examples')
                ->cascadeOnDelete();
            $table->primary(['accessibility_issue_id', 'wcag_criterion_example_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessibility_issue_example');
    }
};
