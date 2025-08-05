<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class TestImport implements ToArray, WithHeadingRow
{
    public function array(array $rows)
    {
        Log::info('Test import - First row headers:', array_keys($rows[0] ?? []));
        Log::info('Test import - First row data:', $rows[0] ?? []);
        
        return [];
    }
} 