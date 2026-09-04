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
        Schema::create('wcag_related_resources', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('wcag_success_criterion_id')->constrained('wcag_success_criteria')->onDelete('cascade');
            $table->enum('type', ['wai_failure', 'project_issue', 'resource_link']);
            $table->string('code')->nullable(); // F3, F13 for WAI failures
            $table->string('title_en');
            $table->string('title_sv');
            $table->text('description_en')->nullable();
            $table->text('description_sv')->nullable();
            $table->string('url')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wcag_related_resources');
    }
};
