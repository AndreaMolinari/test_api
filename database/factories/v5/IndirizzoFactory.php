<?php

namespace Database\Factories\v5;

use App\Models\v5\Indirizzo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Provider\it_IT\Address;

class IndirizzoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Indirizzo::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {        
        $this->faker->addProvider(new Address($this->faker));
        return [
            'istat'     => $this->faker->countryCode(),
            'provincia' => $this->faker->stateAbbr(),
            'nazione'   => $this->faker->country(),
            'comune'    => $this->faker->city(),
            'cap'       => $this->faker->postcode(),
            'via'       => $this->faker->streetName(),
            'civico'    => $this->faker->buildingNumber(),
        ];
    }
}
