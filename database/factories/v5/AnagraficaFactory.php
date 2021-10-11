<?php

namespace Database\Factories\v5;

use App\Models\v5\Anagrafica;
use App\Models\v5\Indirizzo;
use App\Models\v5\Tipologia;
use Faker\Provider\it_IT\Company;
use Faker\Provider\it_IT\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnagraficaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Anagrafica::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $this->faker->addProvider(new Company($this->faker));
        $this->faker->addProvider(new Person($this->faker));

        $genere = Tipologia::where('idParent', 19)->get()->random()->id;

        if ($genere === 20) {
            $return = [
                'nome'    => $this->faker->firstName(),
                'cognome' => $this->faker->lastName(),
                'codFisc' => $this->faker->unique()->taxId(),
            ];
        } elseif ($genere === 21) {
            $return = [
                'ragSoc' => $this->faker->company(),
                'pIva'   => $this->faker->unique()->vatId(),
            ];
        } else {
            $return = [
                'ragSoc'  => $this->faker->company(),
                'codFisc' => $this->faker->unique()->vatId(),
            ];
        }

        return array_merge([
            'idGenere'    => $genere,
            'idOperatore' => 1
        ], $return);
    }

    public function configure()
    {
        return $this->afterCreating(function (Anagrafica $anagrafica) {
            $anagrafica->indirizzi()->syncWithPivotValues(Indirizzo::all()->random()->id, ['idOperatore' => 1, 'idTipologia' => 17]);
        });
    }
}
