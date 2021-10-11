<?php

namespace Database\Factories\v5;

use App\Models\v5\Modello;
use App\Models\v5\Sim;
use Illuminate\Database\Eloquent\Factories\Factory;

class SimFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sim::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'serial'      => '3535353535'.$this->faker->unique()->numberBetween(99999, 999999),
            'idModello'   => Modello::where( 'idTipologia', 11 )->get()->random()->id,
            'apn'         => null,
            'idOperatore' => 1,
        ];
    }
}
