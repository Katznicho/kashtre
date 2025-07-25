<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DynamicTemplateExport implements FromArray, WithHeadings
{
    protected $items;
    protected $businessName;
    protected $branchName;

    public function __construct($businessName, $branchName, $items)
    {
        $this->businessName = $businessName;
        $this->branchName = $branchName;
        $this->items = is_array($items) ? $items : explode(',', $items); // ensure it's an array
    }

    public function headings(): array
    {
        // First columns: business_name, branch_name
        $base = ['Business Name', 'Branch Name'];

        // Convert keys to readable labels
        $itemLabels = array_map(function ($item) {
            return ucwords(str_replace('_', ' ', $item));
        }, $this->items);

        return array_merge($base, $itemLabels);
    }

    public function array(): array
    {
        // Row 1: prefill business and branch name, rest empty
        $row = [
            $this->businessName,
            $this->branchName,
        ];

        foreach ($this->items as $item) {
            $row[] = '';
        }

        return [ $row ];
    }
}
