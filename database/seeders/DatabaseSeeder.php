<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        $this->call([
            TipologiaSeeder::class,
            // BrandSeeder::class,
            // IndirizzoSeeder::class,
            // AnagraficaSeeder::class,
            // AddebitoSeeder::class,
        ]);
        Schema::enableForeignKeyConstraints();
    }
}
