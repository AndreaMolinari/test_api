<?php

namespace Database\Seeders;

use App\Models\v5\Addebito;
use Illuminate\Database\Seeder;

class AddebitoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Addebito::factory()->count(5)->create();
    }
}
