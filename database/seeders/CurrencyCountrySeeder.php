<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Country;
use Illuminate\Database\Seeder;

class CurrencyCountrySeeder extends Seeder
{
    public function run(): void
    {
        $usd = Currency::firstOrCreate(
            ['code' => 'USD'],
            ['name' => 'US Dollar', 'symbol' => '$']
        );

        $ugx = Currency::firstOrCreate(
            ['code' => 'UGX'],
            ['name' => 'Ugandan Shilling', 'symbol' => 'UGX']
        );

        // Default region: United States (USD). Uganda (UGX) retained for regional use.
        Country::firstOrCreate(
            ['iso_code' => 'US'],
            [
                'name' => 'United States',
                'currency_id' => $usd->id,
                'currency_code' => 'USD',
                'exchange_rate_to_usd' => 1,
            ]
        );

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

