<?php

namespace Database\Factories\v5;

use App\Models\v5\DDT;
use App\Models\v5\Modello;
use Illuminate\Database\Eloquent\Factories\Factory;

class DDTFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DDT::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'dataSpedizione'  => $this->faker->date(),
            'colli'           => $this->faker->randomNumber(1),
            'pesoTotale'      => $this->faker->randomFloat(2, 0.1, 12),
            'costoSpedizione' => $this->faker->randomFloat(2, 10, 777),
            'dataOraRitiro'   => $this->faker->dateTime(),
            'idOperatore'     => 1,
        ];
    }
}
