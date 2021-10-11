<?php

namespace Database\Factories\v5;

use App\Models\v5\Componente;
use App\Models\v5\Modello;
use App\Models\v5\Tipologia;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComponenteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Componente::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'unitcode'  => $this->faker->unique()->numberBetween(2000000000, 3000000000),
            'imei'      => $this->faker->unique()->imei(),
        ];
    }
    
    public function radiocomando(){
        return $this->state(function (array $attributes){
            return [
                'idModello' => Modello::where( 'idTipologia', 93 )->get()->random()->id
            ];
        });
    }
    
    public function gps(){
        return $this->state(function (array $attributes){
            return [
                'idModello' => Modello::where( 'idTipologia', 10 )->get()->random()->id
            ];
        });
    }
    
    public function tacho(){
        return $this->state(function (array $attributes){
            return [
                'idModello' => Modello::where( 'idTipologia', 92 )->get()->random()->id
            ];
        });
    }
}
