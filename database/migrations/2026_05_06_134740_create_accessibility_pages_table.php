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
        Schema::create('accessibility_pages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('project_id')->constrained('accessibility_projects')->cascadeOnDelete();
            $table->string('title');
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->enum('scope', ['in_scope', 'out_of_scope'])->default('in_scope');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessibility_pages');
    }
};
