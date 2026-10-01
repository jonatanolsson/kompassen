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
        Schema::table('wcag_criterion_examples', function (Blueprint $table) {
            $table->longText('mermaid_svg')->nullable();
            $table->string('mermaid_source_hash', 64)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wcag_criterion_examples', function (Blueprint $table) {
            $table->dropColumn(['mermaid_svg', 'mermaid_source_hash']);
        });
    }
};
