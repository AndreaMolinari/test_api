<?php

namespace Database\Factories\v5;

use App\Models\v5\Mezzo;
use App\Models\v5\Modello;
use App\Models\v5\Tipologia;
use Illuminate\Database\Eloquent\Factories\Factory;

class MezzoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Mezzo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $plate = $this->faker->randomLetter() . $this->faker->randomLetter();
        $plate .= $this->faker->randomDigit() . $this->faker->randomDigit() . $this->faker->randomDigit();
        $plate .= $this->faker->randomLetter() . $this->faker->randomLetter();
        $plate = strtoupper($plate);

        $telaio = "";
        for ($i = 0; $i < 10; $i++) {
            $telaio .= $this->faker->randomAscii();
        }

        return [
            'targa'     => $plate,
            'telaio'    => $telaio,
            'colore'    => $this->faker->colorName(),
            'idModello' => Modello::where(
                'idTipologia',
                Tipologia::where('idParent', 64)->has('modelli')->get()->random()->id
            )->get()->random()->id,
            'idOperatore' => 1,
        ];
    }
}
