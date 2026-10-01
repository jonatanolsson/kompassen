<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('teams')->orderBy('id')->pluck('id')->each(function (string $teamId): void {
            $firstUserId = DB::table('users')->where('team_id', $teamId)->orderBy('id')->value('id');
            if ($firstUserId !== null) {
                DB::table('teams')->where('id', $teamId)->whereNull('owner_id')->update(['owner_id' => $firstUserId]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Existing team ownership cannot safely be reconstructed after rollback.
    }
};
