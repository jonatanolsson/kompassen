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
        Schema::create('testing_methodologies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('category'); // screen_reader, browser, browser_extension, device, testing_tool
            $table->text('description')->nullable();
            $table->boolean('is_custom')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testing_methodologies');
    }
};
