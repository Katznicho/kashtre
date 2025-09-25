<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixExistingTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:existing-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix migration records for existing tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for existing tables that need migration records...');

        // Check if transactions table exists but migration record is missing
        if (Schema::hasTable('transactions') && !DB::table('migrations')->where('migration', '2025_09_05_171600_create_transactions_table')->exists()) {
            $nextBatch = DB::table('migrations')->max('batch') + 1;
            DB::table('migrations')->insert([
                'migration' => '2025_09_05_171600_create_transactions_table',
                'batch' => $nextBatch
            ]);
            $this->info('✓ Marked transactions migration as run (table already exists)');
        }

        $this->info('✓ Existing tables check completed');
        return 0;
    }
}
