<?php

namespace Database\Factories\v5;

use App\Models\v5\Addebito;
use App\Models\v5\Anagrafica;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddebitoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Addebito::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'descrizione' => $this->faker->sentence(),
            'idAnagrafica' => Anagrafica::all()->random()->id,
            'prezzoUnitario'=> $this->faker->randomFloat(2, 0, 700),
            'sconto'=> [2, 10, 14, 100][rand(0, 3)],
            'iva'=> [22, 4, 10][rand(0, 2)],
        ];
    }
}
