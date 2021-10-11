<?php

use App\Http\Controllers\v5\{
    AddebitoController,
    AnagraficaController,
    ModelloController,
    FatturaController,
    PersonalizzazioniRivenditoreController,
    ServizioController,
    TipologiaController,
    UtenteController,
    BrandController,
    DDTController,
    MezzoController,
    SimController,
};
use App\Models\v5\Fattura;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'superAdmin']], function () {
    // Route::group(['prefix' => 'anagrafiche'], function () {
    //     Route::get('', [AnagraficaController::class, 'index']);
    //     Route::post('ricerca', [AnagraficaController::class, 'search']);
    //     Route::post('', [AnagraficaController::class, 'store']);

    //     Route::group(['prefix' => '{anagrafica}', 'where' => ['anagrafica' => '[0-9]+']], function () {
    //         Route::get('', [AnagraficaController::class, 'show']);
    //         Route::put('', [AnagraficaController::class, 'update']);
    //         Route::delete('', [AnagraficaController::class, 'destroy']);

    //         Route::group(['prefix' => 'utenti'], function () {
    //             Route::get('', [UtenteController::class, 'index']);

    //         });
    //     });
    // });

    Route::prefix('tipologie')->group(function () {
        Route::get('', [TipologiaController::class, 'index']);
        Route::post('', [TipologiaController::class, 'store']);
        Route::prefix('{tipologia}')->group(function () {
            Route::get('', [TipologiaController::class, 'show']);
            Route::put('', [TipologiaController::class, 'update']);
            Route::delete('', [TipologiaController::class, 'destroy']);
        });
    });

    Route::prefix('anagrafiche')->group(function () {
        Route::post('ricerca', [AnagraficaController::class, 'search']);
        Route::get('', [AnagraficaController::class, 'index']);
        Route::post('', [AnagraficaController::class, 'store']);

        Route::prefix('{anagrafica}')->where(['anagrafica' => '[0-9]+'])->group(function () {
            Route::get('', [AnagraficaController::class, 'show']);
            Route::put('', [AnagraficaController::class, 'update']);
            Route::delete('', [AnagraficaController::class, 'destroy']);

            Route::prefix('utenti')->group(function () {
                Route::get('', [UtenteController::class, 'index']);
                Route::post('', [UtenteController::class, 'store']);
                Route::get('{utente}', [UtenteController::class, 'show']);
            });

            Route::prefix('fatture')->group(function () {
                Route::get('', [FatturaController::class, 'index']);
                Route::post('', [FatturaController::class, 'store']);
                Route::post('genera', [FatturaController::class, 'generate']);
                Route::post('proiezione', [FatturaController::class, 'proiezione']);
                Route::prefix('{fattura}')->where(['fattura' => '[0-9]+'])->group(function () {
                    Route::get('', [FatturaController::class, 'show']);
                    Route::put('', [FatturaController::class, 'update']);
                    Route::delete('', [FatturaController::class, 'destroy']);
                });
            });

            Route::prefix('ddts')->group(function () {
                Route::get('', [DDTController::class, 'index']);
                Route::post('', [DDTController::class, 'store']);
                Route::prefix('{ddt}')->where(['ddt' => '[0-9]+'])->group(function () {
                    Route::get('', [DDTController::class, 'show']);
                    Route::put('', [DDTController::class, 'update']);
                    Route::delete('', [DDTController::class, 'destroy']);
                });
            });

            Route::prefix('addebiti')->group(function () {
                Route::get('', [AddebitoController::class, 'index']);
                Route::post('', [AddebitoController::class, 'store']);
                Route::prefix('{addebito}')->where(['addebito' => '[0-9]+'])->group(function () {
                    Route::get('', [AddebitoController::class, 'show']);
                    Route::put('', [AddebitoController::class, 'update']);
                    Route::delete('', [AddebitoController::class, 'destroy']);
                });
            });
        });
    });

    Route::prefix('brands')->group(function () {
        Route::post('ricerca', [BrandController::class, 'search']);
        Route::get('', [BrandController::class, 'index']);
        Route::post('', [BrandController::class, 'store']);

        Route::prefix('{brand}')->where(['brand' => '[0-9]+'])->group(function () {
            Route::get('', [BrandController::class, 'show']);
            Route::put('', [BrandController::class, 'update']);
            Route::delete('', [BrandController::class, 'destroy']);


            Route::prefix('modelli')->group(function () {
                Route::post('ricerca', [ModelloController::class, 'search']);
                Route::get('', [ModelloController::class, 'index']);
                Route::post('', [ModelloController::class, 'store']);

                Route::prefix('{modello}')->where(['modello' => '[0-9]+'])->group(function () {
                    Route::get('', [ModelloController::class, 'show']);
                    Route::put('', [ModelloController::class, 'update']);
                    Route::delete('', [ModelloController::class, 'destroy']);
                });
            });
        });
    });

    Route::prefix('servizi')->group(function () {
        Route::post('ricerca', [ServizioController::class, 'search']);
        Route::get('', [ServizioController::class, 'index']);
        Route::post('', [ServizioController::class, 'store']);

        Route::prefix('{servizio}')->where(['servizio' => '[0-9]+'])->group(function () {
            Route::get('', [ServizioController::class, 'show']);
            Route::put('', [ServizioController::class, 'update']);
            Route::delete('', [ServizioController::class, 'destroy']);
        });
    });

    Route::prefix('mezzi')->group(function () {
        Route::post('ricerca', [MezzoController::class, 'search']);
        Route::get('', [MezzoController::class, 'index']);
        Route::post('', [MezzoController::class, 'store']);

        Route::prefix('{mezzo}')->where(['mezzo' => '[0-9]+'])->group(function () {
            Route::get('', [MezzoController::class, 'show']);
            Route::put('', [MezzoController::class, 'update']);
            Route::delete('', [MezzoController::class, 'destroy']);
        });
    });

    Route::prefix('sims')->group(function () {
        Route::post('ricerca', [SimController::class, 'search']);
        Route::get('', [SimController::class, 'index']);
        Route::post('', [SimController::class, 'store']);

        Route::prefix('{sim}')->where(['sim' => '[0-9]+'])->group(function () {
            Route::get('', [SimController::class, 'show']);
            Route::put('', [SimController::class, 'update']);
            Route::delete('', [SimController::class, 'destroy']);
        });
    });

    Route::prefix('utenti')->group(function () {
        Route::post('ricerca', [UtenteController::class, 'search']);
        Route::get('', [UtenteController::class, 'index']);
        Route::post('', [UtenteController::class, 'store']);

        Route::prefix('{utente}')->where(['utente' => '[0-9]+'])->group(function () {
            Route::get('', [UtenteController::class, 'show']);
            Route::put('', [UtenteController::class, 'update']);
            Route::delete('', [UtenteController::class, 'destroy']);
        });
    });

    Route::prefix('fatture')->group(function () {
        Route::get('', function () {
            return Fattura::all();
        });
        Route::post('genera', [FatturaController::class, 'generate']);
        Route::post('proiezione', [FatturaController::class, 'proiezione']);
        // Route::get('', [FatturaController::class, 'index']);
        // Route::post('', [FatturaController::class, 'store']);
        
        Route::prefix('{fattura}')->where(['fattura' => '[0-9]+'])->group(function () {
            Route::get('pdf', [FatturaController::class, 'pdf']);
            Route::get('', [FatturaController::class, 'show']);
            Route::put('', [FatturaController::class, 'update']);
            Route::delete('', [FatturaController::class, 'destroy']);
        });
    });
});

Route::group(['middleware' => ['auth:api', 'rivenditore']], function () {

    Route::group(['prefix' => 'personalizzazioni'], function () {
        Route::get('{anagrafica?}', [PersonalizzazioniRivenditoreController::class, 'show'])->whereNumber('anagrafica');
        Route::post('{anagrafica?}', [PersonalizzazioniRivenditoreController::class, 'store'])->whereNumber('anagrafica');
    });
});
