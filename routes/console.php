<?php

use App\Http\Controllers\v4\{AaTestController, MailController, ManutenzioneController};
use App\Http\Controllers\v5\ServizioController;
use App\Models\v5\Anagrafica;
use App\Models\v5\Servizio;
use App\Models\v5\Tipologia;
use Facades\App\Repositories\Posizione;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('modificaPiva', function () {
    $services = Anagrafica::whereHas('parent', fn ($q) => $q->where('idParent', 40))
        ->where('idGenere', 22)
        // ->whereRaw("LENGTH(pIva) < 11")
        ->each(function ($item, $key) {
            if ($item->pIva && strlen($item->pIva) < 11) {
                $item->pIva = str_pad($item->pIva, 11, '0', STR_PAD_LEFT);
            }
            $item->idGenere = 21;
            $item->save();
        });

    $this->comment($services);
})->describe('Modifica gli EP di MLS e li rende PG, aggiunge 0 alla partita iva');

Artisan::command('checkPiva', function () {
    $services = Anagrafica::whereHas('parent', fn ($q) => $q->where('idParent', 40))->where('idGenere', 22)->whereRaw("LENGTH(pIva) < 11")->count();
    $this->comment($services);
});

Artisan::command('findDuplicate', function () {
    $services = Anagrafica::select('pIva')->whereNotNull('pIva')->groupBy('pIva')->havingRaw('count(id) > 1')->get();
    $this->comment($services);
});

Artisan::command('fixDuplicate', function () {
    Anagrafica::select('id', 'pIva')->whereNotNull('pIva')->groupBy('pIva')->havingRaw('count(id) > 1')->each(function ($item) {
        $cliente = Anagrafica::where('pIva', $item->pIva)->whereHas('tipologie', fn ($q) => $q->where('TT_Tipologia.id', 12))->with('tipologie')->firstOr(fn () => false);
        $installatore = Anagrafica::where('pIva', $item->pIva)->whereHas('tipologie', fn ($q) => $q->where('TT_Tipologia.id', 26))->with('tipologie')->firstOr(fn () => false);

        if (($cliente && $installatore) && $installatore->servizi()->count() === 0) {
            // print $item->pIva.': '.$cliente->servizi()->count().PHP_EOL;

            // if($installatore->installati()->count() > 0)
            // {
            //     $installatore->installati()->each( function($servizioInstallato) use($cliente){
            //         $servizioInstallato->installatori->sync([$cliente])->save();
            //     });
            // }

            
            $newTipologie = $cliente->tipologie()->get()->unique()->map(fn ($i) => $i->id)->toArray();
            print_r($newTipologie) . PHP_EOL;
            if (!in_array(26, $newTipologie)) {
                $newTipologie[] = 26;
            }

            $cliente->tipologie()->sync($newTipologie)->save();
            dd("askd");
        }
    });
});
