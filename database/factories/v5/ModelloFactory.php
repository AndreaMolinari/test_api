<?php

namespace Database\Factories\v5;

use App\Models\v5\Brand;
use App\Models\v5\Modello;
use App\Models\v5\Tipologia;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModelloFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Modello::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nome' => $this->faker->unique()->word(),
            'idBrand' => Brand::all()->random()->id ?? 0,
            'idOperatore' => 1,
        ];
    }
    
    public function veicolo(){
        return $this->state(function (array $attributes){
            return [
                'idTipologia' => Tipologia::where( 'idParent', 64 )->get()->random()->id
            ];
        });
    }
    
    public function radiocomando(){
        return $this->state(function (array $attributes){
            return [
                'idTipologia' => 93
            ];
        });
    }
    
    public function gps(){
        return $this->state(function (array $attributes){
            return [
                'idTipologia' => 10
            ];
        });
    }
    
    public function tacho(){
        return $this->state(function (array $attributes){
            return [
                'idTipologia' => 92
            ];
        });
    }
    
    public function sim(){
        return $this->state(function (array $attributes){
            return [
                'idTipologia' => 11
            ];
        });
    }
}
