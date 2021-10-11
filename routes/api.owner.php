<?php

// use App\Http\Controllers\AuthController;
// use App\Http\Controllers\v3\TraxController;

use App\Http\Controllers\v4\TraxController;
use App\Http\Requests\Trax\ParzialeRequest;
use App\Models\v5\Anagrafica;
use App\Models\v5\Flotta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => []], function(){
    Route::get('flotta', [TraxController::class, 'utenti_flotte']);
    Route::group(['prefix' => 'posizione'], function(){
        Route::post('{params}/{id}', [TraxController::class, 'flotta_posizione'])->where('params', 'flotta')->where('id', '[0-9]+');
        Route::post('servizio/{id}', [TraxController::class, 'mezzo_posizione'])->where('id', '[0-9]+');
    });
    Route::post('storico/{id}', [TraxController::class, 'storico'])->where('id', '[0-9]+');

    // Route::post('anthea/{id}', function(ParzialeRequest $req, $id){
    //     return Flotta::byUtente(Anagrafica::find(17)->utenti()->first()->id)->with('servizi')->get();
    //     $total_data = collect((new TraxController)->parziale($req, $id))->map( fn($item) => $item->globale );
    //     return $total_data;
    // });
});
