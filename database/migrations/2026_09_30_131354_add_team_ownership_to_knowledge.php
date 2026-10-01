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
        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_knowledge_editor')->default(false);
        });
        Schema::table('wcag_criterion_examples', function (Blueprint $table) {
            $table->foreignUlid('team_id')->nullable()->constrained('teams')->cascadeOnDelete();
        });
        Schema::table('testing_methodologies', function (Blueprint $table) {
            $table->foreignUlid('team_id')->nullable()->constrained('teams')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testing_methodologies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });
        Schema::table('wcag_criterion_examples', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_knowledge_editor');
        });
        Schema::table('teams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }
};
