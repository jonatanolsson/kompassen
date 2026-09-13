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
        Schema::create('wcag_criterion_example_links', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('wcag_criterion_example_id')
                ->constrained('wcag_criterion_examples')
                ->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('label')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wcag_criterion_example_links');
    }
};
