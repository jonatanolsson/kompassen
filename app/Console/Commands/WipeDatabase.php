<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('db:wipe-all')]
#[Description('Drop all tables in the database (with confirmation)')]
class WipeDatabase extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();
        
        // Get all tables using information_schema
        $tables = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?", [$database]);
        $tableNames = array_map(fn ($table) => $table->TABLE_NAME, $tables);

        if (empty($tableNames)) {
            $this->info('No tables found in the database.');
            return;
        }

        $this->info('Tables to be dropped:');
        foreach ($tableNames as $table) {
            $this->line("  - <fg=red>{$table}</>");
        }

        $this->newLine();

        if (!$this->confirm('Are you sure you want to drop all tables? This cannot be undone.')) {
            $this->info('Operation cancelled.');
            return;
        }

        if (!$this->confirm('⚠️  LAST CONFIRMATION: Drop ALL tables permanently?')) {
            $this->info('Operation cancelled.');
            return;
        }

        try {
            // Disable foreign key checks
            $connection->statement('SET FOREIGN_KEY_CHECKS=0');

            // Drop all tables
            foreach ($tableNames as $table) {
                Schema::drop($table);
                $this->line("  Dropped: <fg=green>{$table}</>");
            }

            // Re-enable foreign key checks
            $connection->statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info('✓ All tables have been dropped successfully.');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}

