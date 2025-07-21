<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListItems extends Command
{
    protected $signature = 'list:items';
    protected $description = 'List all items in the items table';

    public function handle()
    {
        try {
            $items = DB::select('SELECT * FROM items');
            $this->info('Items in table:');
            foreach ($items as $item) {
                $this->line(json_encode($item));
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
