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
            $table->foreignId('assigned_to')
                ->nullable()
                ->after('project_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamp('assigned_at')
                ->nullable()
                ->after('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accessibility_issues', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['assigned_to']);
            $table->dropColumn(['assigned_to', 'assigned_at']);
        });
    }
};
