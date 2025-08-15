<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Database\Seeders\KashtreSeeder;
use Database\Seeders\DummyDataSeeder;
use Database\Seeders\TestDataSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call([
            KashtreSeeder::class,
            TestDataSeeder::class,
        ]);
    }
}
