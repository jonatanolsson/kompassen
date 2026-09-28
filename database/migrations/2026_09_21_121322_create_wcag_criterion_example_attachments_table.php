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
        Schema::create('wcag_criterion_example_attachments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('wcag_criterion_example_id');
            $table->foreign('wcag_criterion_example_id', 'wcag_example_attachment_example_fk')
                ->references('id')
                ->on('wcag_criterion_examples')
                ->cascadeOnDelete();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wcag_criterion_example_attachments');
    }
};
