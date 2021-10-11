<?php

namespace Database\Factories\v5;

use App\Models\v5\Utente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UtenteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Utente::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'username' => $this->faker->unique()->userName(),
            'password' => Hash::make('password'),
            'email' => $this->faker->unique()->email(),
            'idOperatore' => 1
        ];
    }
}
