<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Database\Seeders\KashtreSeeder;
use Database\Seeders\DummyDataSeeder;


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
            DummyDataSeeder::class,
        ]);
    }
}
