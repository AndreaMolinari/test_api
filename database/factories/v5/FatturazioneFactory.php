<?php

namespace Database\Factories\v5;

use App\Models\v5\Fatturazione;
use App\Models\v5\Tipologia;
use Faker\Provider\it_IT\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class FatturazioneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Fatturazione::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $this->faker->addProvider(new Company($this->faker));

        // $modalita    = Tipologia::where('idParent', 38)->get()->random()->id;
        // $cadenza     = Tipologia::where('idParent', 39)->get()->random()->id;
        // $periodicita = Tipologia::where('idParent', 50)->get()->random()->id;
        
        return [
            'sdi'             => $this->faker->word(),
            'splitPA'         => $this->faker->boolean(),
            'esenteIVA'       => $this->faker->boolean(),
            'speseIncasso'    => $this->faker->boolean(),
            'speseSpedizione' => $this->faker->boolean(),
            'banca'           => null,
            'filiale'         => null,
            'iban'            => $this->faker->iban(),
            'pec'             => $this->faker->unique()->email(),
            'mail'            => $this->faker->email(),
            'idModalita'      => Tipologia::where('idParent', 38)->get()->random()->id,
            'idPeriodo'       => Tipologia::where('idParent', 39)->get()->random()->id,
        ];
    }
}
