<?php

namespace Database\Seeders;

use App\Models\v5\Indirizzo;
use Illuminate\Database\Seeder;

class IndirizzoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Indirizzo::factory()->count(50)->create();
    }
}
