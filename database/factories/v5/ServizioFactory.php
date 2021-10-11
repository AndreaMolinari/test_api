<?php

namespace Database\Factories\v5;

use App\Models\v5\{Anagrafica, Installatore, Servizio, Tipologia};
use Illuminate\Database\Eloquent\Factories\Factory;

class ServizioFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Servizio::class;

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Servizio $servizio) {
            $servizio->applicativi()->syncWithPivotValues(Tipologia::where('idParent', 83)->get()->random()->id, ['idOperatore' => 1]);
            $servizio->installatori()->syncWithPivotValues(Installatore::all()->random()->id, ['idOperatore' => 1]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'idAnagrafica' => Anagrafica::all()->random()->id,
            'dataInizio'   => $this->faker->date(),
            'dataFine'     => null,
            'prezzo'       => $this->faker->numberBetween(0, 500),
            'idPeriodo'    => Tipologia::where('idParent', 50)->get()->random()->id,
            'idCausale'    => Tipologia::where('idParent', 91)->get()->random()->id,
            'idOperatore'  => 1,
        ];
    }
}
