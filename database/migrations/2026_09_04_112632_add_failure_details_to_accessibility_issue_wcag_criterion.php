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
        Schema::table('accessibility_issue_wcag_criterion', function (Blueprint $table) {
            $table->string('failure_type')->nullable()->after('wcag_success_criterion_id');
            $table->text('comment')->nullable()->after('failure_type');
            $table->text('code_snippet')->nullable()->after('comment');
            $table->string('screenshot_path')->nullable()->after('code_snippet');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accessibility_issue_wcag_criterion', function (Blueprint $table) {
            $table->dropColumn(['failure_type', 'comment', 'code_snippet', 'screenshot_path', 'created_at', 'updated_at']);
        });
    }
};
