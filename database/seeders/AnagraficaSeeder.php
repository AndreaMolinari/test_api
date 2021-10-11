<?php

namespace Database\Seeders;

use App\Models\v5\Anagrafica;
use App\Models\v5\Componente;
use App\Models\v5\DDT;
use App\Models\v5\Fatturazione;
use App\Models\v5\Mezzo;
use App\Models\v5\Servizio;
use App\Models\v5\Sim;
use App\Models\v5\Tipologia;
use App\Models\v5\Utente;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AnagraficaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Anagrafica::firstOrCreate([
            'nome'        => 'Andrea',
            'cognome'     => 'Molinari',
            'idGenere'    => 20,
            'codFisc'     => 'MLNNDR91A18I712E',
            'dataNascita' => '1991-01-18',
            'idOperatore' => 1
        ])->utenti()->saveMany([
            Utente::firstOrCreate([
                'username'    => 'iMatto',
                'email'       => 'molinari@live.it',
                'idTipologia' => 98,
                'idOperatore' => 1
            ], [
                'password'    => Hash::make('porcoddio'),
            ])
        ]);

        Anagrafica::factory()
            ->hasUtenti(1)
            ->hasFatturazione(1)
            ->has(
                Servizio::factory()
                    ->has(
                        Componente::factory()->gps()->count(rand(1, 2)), 'gps'
                    )
                    ->has(
                        Componente::factory()->tacho()->count(rand(0, 1)), 'tacho'
                    )
                    ->has(
                        Componente::factory()->radiocomando()->count(rand(0, 10)), 'radiocomandi'
                    )->has(
                        Mezzo::factory()->count(1), 'mezzo'
                    )
                    // ->count(rand(5, 10)), 'servizi'
                    ->count(2), 'servizi'
            )
            ->has(
                DDT::factory()
                    ->has(
                        Componente::factory()->gps()->count(2), "componenti"
                    )
                    ->has(
                        Componente::factory()->tacho()->count(1), "componenti"
                    )
                    ->has(
                        Sim::factory()->count(3), "sims"
                    )
                    ->count(1), 'ddts'
            )
            ->count(10)->create();
    }
}
