<?php

namespace Database\Seeders;

use App\Models\v5\Brand;
use App\Models\v5\Modello;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Brand::factory()->has( Modello::factory()->count(2)->gps(), 'modelli' )->count(1)->create();
        
        Brand::factory()->has( Modello::factory()->tacho()->count(2), 'modelli' )->count(1)->create();
        
        Brand::factory()->has( Modello::factory()->radiocomando()->count(1), 'modelli' )->count(1)->create();
        
        Brand::factory()->has( Modello::factory()->veicolo()->count(5), 'modelli' )->count(1)->create();

        Brand::factory()->has( Modello::factory()->sim()->count(5), 'modelli' )->count(2)->create();
    }
}
