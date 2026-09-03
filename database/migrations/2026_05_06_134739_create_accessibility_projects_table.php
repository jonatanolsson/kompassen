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
        Schema::create('accessibility_projects', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('target_wcag_level', ['A', 'AA', 'AAA'])->default('AA');
            $table->enum('target_wcag_version', ['2.0', '2.1'])->default('2.1');
            $table->dateTime('audit_date')->nullable();
            $table->enum('status', ['planning', 'in_progress', 'completed'])->default('planning');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessibility_projects');
    }
};
