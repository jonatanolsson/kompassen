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
        Schema::table('accessibility_issues', function (Blueprint $table) {
            $table->string('resolution_status')->default('open')->nullable(); // open, fixed, wontfix
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accessibility_issues', function (Blueprint $table) {
            $table->dropColumn(['resolution_status', 'resolution_notes', 'resolved_at']);
        });
    }
};
