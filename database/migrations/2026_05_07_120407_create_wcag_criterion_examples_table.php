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
        Schema::create('wcag_criterion_examples', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('wcag_success_criterion_id')
                ->constrained('wcag_success_criteria')
                ->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('code_snippet')->nullable();
            $table->string('code_language')->nullable()->default('html');
            $table->string('url')->nullable();
            $table->string('url_label')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wcag_criterion_examples');
    }
};
