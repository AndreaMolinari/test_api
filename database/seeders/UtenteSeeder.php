<?php

namespace Database\Seeders;

use App\Models\v5\Utente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UtenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Schema::disableForeignKeyConstraints();
        // Utente::firstOrCreate([
        //     'username'    => 'iMatto',
        //     'password'    => Hash::make('porcoddio'),
        //     'email'       => 'ferrari',
        //     'idTipologia' => 1,
        //     'idOperatore' => 1
        // ]);
        // Schema::enableForeignKeyConstraints();
    }
}
