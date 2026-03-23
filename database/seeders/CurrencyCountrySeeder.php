<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CurrencyCountrySeeder extends Seeder
{
    public function run(): void
    {
        $ugx = Currency::firstOrCreate(
            ['code' => 'UGX'],
            ['name' => 'Ugandan Shilling', 'symbol' => 'UGX']
        );

        // Uganda ISO code is "UG"
        Country::firstOrCreate(
            ['iso_code' => 'UG'],
            [
                'name' => 'Uganda',
                'currency_id' => $ugx->id,
                'currency_code' => 'UGX',
                'exchange_rate_to_usd' => 1,
            ]
        );
    }
}

