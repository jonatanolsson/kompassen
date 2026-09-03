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
        Schema::create('accessibility_project_methodologies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('project_id');
            $table->ulid('methodology_id');
            $table->text('notes')->nullable(); // Specifik version/enhet/konfiguration
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('accessibility_projects')->cascadeOnDelete();
            $table->foreign('methodology_id')->references('id')->on('testing_methodologies')->cascadeOnDelete();
            $table->unique(['project_id', 'methodology_id'], 'project_methodology_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessibility_project_methodologies');
    }
};
