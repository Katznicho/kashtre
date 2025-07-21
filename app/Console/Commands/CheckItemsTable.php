<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckItemsTable extends Command
{
    protected $signature = 'check:items-table';
    protected $description = 'Check the structure of the items table';

    public function handle()
    {
        try {
            $columns = DB::select('SHOW COLUMNS FROM items');
            $this->info('Table structure:');
            foreach ($columns as $column) {
                $this->line(json_encode($column));
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
