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
        Schema::table('wcag_success_criteria', function (Blueprint $table) {
            $table->text('description_en')->nullable()->change();
            $table->text('description_sv')->nullable()->change();
            $table->string('url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wcag_success_criteria', function (Blueprint $table) {
            $table->text('description_en')->nullable(false)->change();
            $table->text('description_sv')->nullable(false)->change();
            $table->string('url')->nullable(false)->change();
        });
    }
};
