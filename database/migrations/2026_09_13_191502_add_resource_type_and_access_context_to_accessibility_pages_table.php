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
        Schema::table('accessibility_pages', function (Blueprint $table): void {
            $table->string('resource_type')->default('page')->after('name');
            $table->string('access_context')->default('not_applicable')->after('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accessibility_pages', function (Blueprint $table): void {
            $table->dropColumn(['resource_type', 'access_context']);
        });
    }
};
