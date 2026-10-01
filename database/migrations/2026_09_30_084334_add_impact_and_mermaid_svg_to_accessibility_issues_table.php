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
            $table->longText('impact')->nullable()->after('description');
            $table->longText('mermaid_svg')->nullable()->after('mermaid_diagram');
            $table->string('mermaid_source_hash', 64)->nullable()->after('mermaid_svg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accessibility_issues', function (Blueprint $table) {
            $table->dropColumn(['impact', 'mermaid_svg', 'mermaid_source_hash']);
        });
    }
};
