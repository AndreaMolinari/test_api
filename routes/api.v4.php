<?php

use App\Common\Managers\PosizioniManager;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\v4\{
    AaTestController,
    AnagraficaController,
    AuthController,
    AutistaController,
    BrandController,
    CampoAnagraficaController,
    ComponenteController,
    DDTController,
    EventoController,
    FlottaController,
    IndirizzoController,
    ManutenzioneController,
    MezzoController,
    ModelloController,
    ModulazioneUsciteController,
    RssController,
    ServizioController,
    SimController,
    StrangeController,
    TipologiaController,
    TorinoController,
    TraxController
};
use App\Http\Controllers\v4\Targets\{TriggerEventoAreaController, TargetController, NotificaAreaController, SogliaController, TriggerEventoSogliaController};
use App\Http\Controllers\v4\Trax\ContattoController;
use App\Jobs\CheckTargetJobNew;
use App\Models\Targets\TT_StoricoEventoModel;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;

Route::group(['prefix' => 'heartbeat', 'middleware' => ['auth:api']], function () {
    Route::post('', [TraxController::class, 'set_heartbeat']);
    Route::get('',  [TraxController::class, 'get_heartbeat']);
});

Route::group(['middleware' => ['auth:api', 'superAdmin']], function () {

    Route::get('healthCheckPosizioni', [ServizioController::class, 'check_posizioni']);

    if (!App::environment('produzione')) {
        Route::group(['prefix' => 'testolina'], function () {
            Route::post('frocio/{idServizio}', [ModulazioneUsciteController::class, 'setStatus'])->whereNumber('idServizio');
            Route::post('', function () {

                return response()->json(['message' => 'test'], 418);

                // return (new TriggerEventoAreaController)->tmpPort();
                // return (new TriggerEventoAreaController)->store($req, 1, 3677);

                Redis::set('TESTlatest_pos', json_encode(PosizioniManager::fetchAndUpdateLatests()));

                CheckTargetJobNew::dispatchSync('TESTlatest_pos');

                // (new CheckTargetJobNew('TESTlatest_pos'))->handle();

                return response()->json(['message' => 'testolina'], 418);
                /** @var TT_StoricoEventoModel */
                // $asd = TT_StoricoEventoModel::make();
                //                 $asd->posizione = ['dio' => 'cane'];
                //                 $asd->servizio()->associate(2);
                //                 $asd->save();
                //                 return $asd;
            });
        });
    }

    Route::group(['prefix' => 'utente'], function () {
        Route::delete('{id}', [AuthController::class, 'delete'])->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'anagrafica'], function () {
        Route::delete('{id}', [AnagraficaController::class, 'delete'])->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'servizio'], function () {
        Route::delete('{id}', [ServizioController::class, 'delete'])->where('id', '[0-9]+');
        Route::get('sanitize', [ServizioController::class, 'sanitize']);
        Route::get('sanitizeApplicativiDups', [ServizioController::class, 'sanitizeApplicativiDups']);
    });

    Route::group(['prefix' => 'mezzo'], function () {
        Route::get('sanitize', [MezzoController::class, 'sanitize']);
    });

    Route::group(['prefix' => 'modello'], function () {
        Route::get('sanitize', [ModelloController::class, 'sanitize']);
    });

    Route::group(['prefix' => 'brand'], function () {
        Route::get('sanitize', [BrandController::class, 'sanitize']);
    });
});

Route::group(['middleware' => ['auth:api', 'operatore']], function () {
    Route::group(['prefix' => 'mezzo'], function () {
        Route::get('', [MezzoController::class, 'get_all']);
        Route::get('{id}', [MezzoController::class, 'get_id'])->where('id', '[0-9]+');
        Route::post('', [MezzoController::class, 'create']);
        Route::put('{id}', [MezzoController::class, 'create'])->where('id', '[0-9]+');
        Route::delete('{id}', [MezzoController::class, 'delete'])->where('id', '[0-9]+');

        Route::get('nonassociato/{idServizio?}', [MezzoController::class, 'non_associato'])->where('idServizio', '[0-9]+');
    });

    Route::group(['prefix' => 'sim'], function () {
        Route::get('', [SimController::class, 'get_all']);
        Route::get('{id}', [SimController::class, 'get_id'])->where('id', '[0-9]+');

        Route::post('', [SimController::class, 'create']);
        Route::put('{id}', [SimController::class, 'create'])->where('id', '[0-9]+');

        Route::group(['prefix' => 'nonassociato'], function () {
            Route::get('{params?}/{id?}', [SimController::class, 'get_unassociated'])->where('params', 'servizio|componente')->where('id', '[0-9]+');
        });
    });

    Route::group(['prefix' => 'componente'], function () {
        Route::get('', [ComponenteController::class, 'get_all_gps']);
        Route::get('{id}', [ComponenteController::class, 'get_id'])->where('id', '[0-9]+');
        Route::get('nonassociato/{id?}', [ComponenteController::class, 'non_associato_gps'])->where('id', '[0-9]+');
        Route::post('bulk', [ComponenteController::class, 'create_bulk']);
        Route::post('', [ComponenteController::class, 'create']);
        Route::put('{id}', [ComponenteController::class, 'create'])->where('id', '[0-9]+');
        Route::delete('{id}', [ComponenteController::class, 'delete'])->where('id', '[0-9]+');

        Route::put('ricalcolaindirizzi/{id}', [TraxController::class, 'rigenera_indirizzi'])->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'indirizzo'], function () {
        Route::post('',                [IndirizzoController::class, 'create']);
        Route::put('{id}',             [IndirizzoController::class, 'create'])->where('id', '[0-9]+');
        Route::get('',                 [IndirizzoController::class, 'get_all']);
        Route::get('{id}',             [IndirizzoController::class, 'get_id'])->where('id', '[0-9]+');
        Route::get('findCap/{cap}',    [IndirizzoController::class, 'find_cap'])->where('cap', '[0-9]{5}');
    });

    Route::group(['prefix' => 'radiocomando'], function () {
        Route::post('', [ComponenteController::class, 'create_radiocomando']);
        Route::get('', [ComponenteController::class, 'get_all_radiocomando']);
        Route::put('{id}', [ComponenteController::class, 'create_radiocomando'])->where('id', '[0-9]+');
        Route::get('{id}', [ComponenteController::class, 'get_id'])->where('id', '[0-9]+');
        Route::get('nonassociato/{id?}', [ComponenteController::class, 'non_associato_radiocomando'])->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'tacho'], function () {
        Route::get('', [ComponenteController::class, 'get_all_tacho']);
        Route::post('', [ComponenteController::class, 'create_tacho']);
        Route::put('{id}', [ComponenteController::class, 'create_tacho'])->where('id', '[0-9]+');
        Route::get('{id}', [ComponenteController::class, 'get_id'])->where('id', '[0-9]+');
        Route::get('nonassociato/{id?}', [ComponenteController::class, 'non_associato_tacho'])->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'evento'], function () {
        Route::get('', [EventoController::class, 'get_all']);
        Route::get('{id}', [EventoController::class, 'get_id']);

        Route::post('', [EventoController::class, 'create']);
        Route::put('{id}', [EventoController::class, 'create'])->where('id', '[0-9]+');

        Route::delete('{id}', [EventoController::class, 'delete'])->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'ddts'], function () {
        Route::get('', [DDTController::class, 'index']);
        Route::get('{ddt}', [DDTController::class, 'show']);

        Route::post('', [DDTController::class, 'store']);
        Route::put('{ddt}', [DDTController::class, 'update'])->whereNumber('ddt');

        Route::delete('{ddt}', [DDTController::class, 'destroy'])->whereNumber('ddt');
    });
});

Route::group(['middleware' => ['auth:api', 'rivenditore']], function () {

    Route::post('check_mls', [AaTestController::class, 'mls_scad']);

    Route::group(['prefix' => 'anagrafica'], function () {
        Route::get('{params?}/{id?}', [AnagraficaController::class, 'get_all'])->where('params', 'latests|tipologia|short')->where('id', '[0-9]+');
        Route::get('{id}', [AnagraficaController::class, 'get_id'])->whereNumber('id');

        Route::post('', [AnagraficaController::class, 'create']);
        Route::post('ricerca', [AnagraficaController::class, 'ricerca']);
        Route::put('{id}', [AnagraficaController::class, 'create'])->where('id', '[0-9]+');
        Route::post('validpiva', [AnagraficaController::class, 'get_piva_info']);

        Route::get('fatturazione/iban/{iban}', [AnagraficaController::class, 'valid_iban']);
    });

    Route::group(['prefix' => 'servizio'], function () {
        Route::get('{params?}', [ServizioController::class, 'get_all'])->where('params', 'latests|attivo|scaduto|futuro');
        Route::get('{id}', [ServizioController::class, 'get_id'])->whereNumber('id');
        Route::get('flotta/{id?}', [ServizioController::class, 'get_servizi_non_in_flotta']);

        Route::get('{params?}/{id?}', [ServizioController::class, 'get_per'])->where('params', 'applicativo|anagrafica');

        Route::post('', [ServizioController::class, 'create']);
        Route::put('{id}', [ServizioController::class, 'create'])->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'utente'], function () {
        Route::get('', [AuthController::class, 'get_all']);
        Route::get('{id}', [AuthController::class, 'get_id'])->where('id', '[0-9]+');
        Route::post('checkusername', [AuthController::class, 'username_available'])->where('id', '[0-9]+');
        Route::post('', [AuthController::class, 'create']);
        Route::put('{id}', [AuthController::class, 'create'])->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'flotta'], function () {
        Route::get('', [FlottaController::class, 'get_all']);
        Route::get('{id}', [FlottaController::class, 'get_id'])->where('id', '[0-9]+');

        Route::post('', [FlottaController::class, 'create']);
        Route::put('{id}', [FlottaController::class, 'create'])->where('id', '[0-9]+');

        Route::delete('{id}', [FlottaController::class, 'delete'])->where('id', '[0-9]+');

        Route::get('{param}/{id}', [FlottaController::class, 'get_by_params'])->where('param', 'utente|servizio')->where('id', '[0-9]+');
        Route::get('sanitize/{id}', [FlottaController::class, 'sanitize'])->where('id', '[0-9]+');

        Route::delete('{id}/{param}/{idServizio}', [FlottaController::class, 'delete_servizio_from_flotta'])->where('id', '[0-9]+')->where('param', 'servizio|utente')->where('idServizio', '[0-9]+');
    });
});

Route::group(['middleware' => ['auth:api', 'superUser']], function () {
    // * 🅼🅰🅽🆄🆃🅴🅽🆉🅸🅾🅽🅴
    Route::group(['prefix' => 'manutenzione'], function () {
        Route::get('', [ManutenzioneController::class, 'getAll']);
        Route::get('{status}', [ManutenzioneController::class, 'getAll'])->where('status', 'opened|closed|expiring');
        Route::post('', [ManutenzioneController::class, 'create']);
        Route::get('{id}', [ManutenzioneController::class, 'getId'])->where('id', '[0-9]+');
        Route::post('confirm/{id}', [ManutenzioneController::class, 'confirm'])->where('id', '[0-9]+');
        Route::put('confirm/{id}', [ManutenzioneController::class, 'confirm'])->where('id', '[0-9]+');
        Route::put('{id}', [ManutenzioneController::class, 'edit'])->where('id', '[0-9]+');
        Route::delete('{id}', [ManutenzioneController::class, 'delete'])->where('id', '[0-9]+');
        Route::get('servizio/{id}', [ManutenzioneController::class, 'findFromServizio'])->where('id', '[0-9]+');
        Route::get('servizio/{id}/{status}', [ManutenzioneController::class, 'findFromServizio'])->where('id', '[0-9]+')->where('status', 'opened|closed|expiring');
        Route::get('flotta/{id}', [ManutenzioneController::class, 'findFromFlotta'])->where('id', '[0-9]+');
        Route::get('flotta/{id}/{status}', [ManutenzioneController::class, 'findFromFlotta'])->where('id', '[0-9]+')->where('status', 'opened|closed|expiring');
        Route::get('custom/{params}', [ManutenzioneController::class, 'get_all_custom_tipologia'])->where('params', 'email|tipologia|officina');
        Route::put('custom/{id}', [ManutenzioneController::class, 'edit_custom_tipologia'])->where('id', '[0-9]+');
        Route::delete('custom/{id}', [ManutenzioneController::class, 'delete_custom_tipologia'])->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'campoanagrafica'], function () { // ! PROBLEMA SERIO
        Route::get('{params}/{id}', 'v3\CampoAnagraficaController@getByUtenteNome')->where('params', '[A-z]+')->where('id', '[0-9]+');
        Route::get('{idTipologia}/{idUtente}', [CampoAnagraficaController::class, 'getByTipologiaUtente'])->where('idTipologia', '[0-9]+')->where('idUtente', '[0-9]+');
        Route::post('', 'v3\CampoAnagraficaController@insert');
        Route::delete('{idCampoAnagrafica}', [CampoAnagraficaController::class, 'destroy'])->where('idCampoAnagrafica', '[0-9]+');
    });
});

Route::group(['middleware' => ['auth:api', 'userFleetManager']], function () {

    Route::group(['prefix' => 'Trax'], function () {
        Route::get('tachigrafo', [TraxController::class, 'tachigrafo']);

        Route::post('InOut/{id}', [ModulazioneUsciteController::class, 'setStatus'])->where('id', '[0-9]+');
        Route::post('newPosizione/{id}', [TorinoController::class, 'newPosition'])->where('id', '[0-9]+');

        Route::group(['prefix' => 'custom'], function () {
            Route::post('nickname/{idFlotta}', [FlottaController::class, 'custom_flotta'])
                ->where('idFlotta', '[0-9]+');
            Route::post('flotta/principale/{idUtente}/{idFlotta}', [FlottaController::class, 'set_principale_flotta'])
                ->where('idUtente', '[0-9]+')
                ->where('idServizio', '[0-9]+');
            Route::post('mezzo/{idServizio}', [TraxController::class, 'resolve_real_mezzo'])
                ->where('idServizio', '[0-9]+');
        });

        Route::group(['prefix' => 'strangefn'], function () {
            Route::get('', [StrangeController::class, 'get_info_periferica']);
            Route::post('{idServizio}', [StrangeController::class, 'set_info_periferica'])
                ->where('idServizio', '[0-9]+');
        });

        Route::get('test/{idUtente}', [TorinoController::class, 'getReportMensilePerUtente'])->where('idUtente', '[0-9]+');

        Route::group(['prefix' => 'radiocomando'], function () {
            Route::get('{id?}', [ComponenteController::class, 'get_radiocomandi_per_anagrafica'])->where('id', '[0-9]+');
            Route::post('{id?}', [ComponenteController::class, 'associa_autista'])->where('id', '[0-9]+');
        });
        Route::group(['prefix' => 'autista'], function () {
            Route::put('{id?}', [AutistaController::class, 'update'])->where('id', '[0-9]+');
        });

        Route::group(['prefix' => 'autisti'], function () {
            Route::post('', [AutistaController::class, 'store']);
            // Route::get('', [AutistaController::class, 'index']);
            Route::get('{id}', [AutistaController::class, 'show']);
            Route::get('utente/{id?}', [AutistaController::class, 'get_autista_from_anagrafica'])->where('id', '[0-9]+');
            Route::put('{id}', [AutistaController::class, 'update']);
            Route::delete('{id}', [AutistaController::class, 'destroy']);
            Route::get('trash', [AutistaController::class, 'index_trash']);
            Route::post('trash/{id}', [AutistaController::class, 'restore_trash']);
            Route::delete('trash/{id}', [AutistaController::class, 'destroy_trash']);
        });

        Route::group(['prefix' => 'mesaroli'], function () {
            Route::get('{idFlotta}',    [TorinoController::class, 'get_mesaroli'])->where('idFlotta', '[0-9]+');
            Route::put('{idFlotta}/{idServizio}',  [TorinoController::class, 'set_mesaroli'])->where('idFlotta', '[0-9]+')->where('idServizio', '[0-9]+');
        });


        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
        Route::group(['prefix' => 'target'], function () {
            Route::post('valid/{idUtente}', [TargetController::class, 'valid'])->whereNumber('idUtente');
            Route::get('{scope}/{idScoped?}', [TargetController::class, 'get_all'])
                ->where('scope', 'flotta|servizio|utente')
                ->whereNumber('idScoped');
            Route::get('{idArea}', [TargetController::class, 'get'])
                ->whereNumber('idArea');
            Route::post('{idUtente?}', [TargetController::class, 'create'])
                ->whereNumber('idUtente');
            Route::put('{idArea}', [TargetController::class, 'update'])
                ->whereNumber('idArea');
            Route::delete('{idArea}', [TargetController::class, 'delete'])
                ->whereNumber('idArea');


            Route::group(['prefix' => '{idArea}/notifica'], function () {
                Route::get('', [NotificaAreaController::class, 'get_all']);
                Route::get('{idNotifica}', [NotificaAreaController::class, 'get'])
                    ->whereNumber('idNotifica');
                Route::post('', [NotificaAreaController::class, 'create']);
                Route::put('{idNotifica}', [NotificaAreaController::class, 'update'])
                    ->whereNumber('idNotifica');
                Route::delete('{idNotifica}', [NotificaAreaController::class, 'delete'])
                    ->whereNumber('idNotifica');
            });

            Route::group(['prefix' => '{idArea}/notifica2'], function () {
                Route::get('', [TriggerEventoAreaController::class, 'index']);
                Route::get('{idTriggerEvento?}', [TriggerEventoAreaController::class, 'show'])
                    ->whereNumber('idTriggerEvento');
                Route::post('', [TriggerEventoAreaController::class, 'store']);
                Route::put('{idTriggerEvento}', [TriggerEventoAreaController::class, 'update'])
                    ->whereNumber('idTriggerEvento');
                Route::delete('{idTriggerEvento}', [TriggerEventoAreaController::class, 'delete'])
                    ->whereNumber('idTriggerEvento');
            });
        });

        Route::group(['prefix' => 'contatti'], function () {
            Route::get('{idUtente?}', [ContattoController::class, 'index'])->whereNumber('idUtente');
            Route::post('{idUtente?}', [ContattoController::class, 'store'])->whereNumber('idUtente');
            Route::put('{idContatto}', [ContattoController::class, 'update'])->whereNumber('idContatto');
            Route::delete('{idContatto}', [ContattoController::class, 'destroy'])->whereNumber('idContatto');
        });

        Route::group(['prefix' => 'soglia'], function () {
            Route::get('utente/{idUtente?}', [SogliaController::class, 'get_all'])
                ->whereNumber('idUtente');
            Route::get('{idSoglia}', [SogliaController::class, 'get'])
                ->whereNumber('idSoglia');
            Route::post('{idUtente?}', [SogliaController::class, 'create'])
                ->whereNumber('idUtente');
            Route::put('{idSoglia}', [SogliaController::class, 'update'])
                ->whereNumber('idSoglia');
            Route::delete('{idSoglia}', [SogliaController::class, 'delete'])
                ->whereNumber('idSoglia');

            Route::group(['prefix' => '{idSoglia}/notifica'], function () {
                Route::get('', [TriggerEventoSogliaController::class, 'index']);
                Route::get('{idTriggerEvento?}', [TriggerEventoSogliaController::class, 'show'])
                    ->whereNumber('idTriggerEvento');
                Route::post('', [TriggerEventoSogliaController::class, 'store']);
                Route::put('{idTriggerEvento}', [TriggerEventoSogliaController::class, 'update'])
                    ->whereNumber('idTriggerEvento');
                Route::delete('{idTriggerEvento}', [TriggerEventoSogliaController::class, 'delete'])
                    ->whereNumber('idTriggerEvento');
            });
        });

        Route::post('{trigger}/storico/{idTrigger?}', [TraxController::class, 'storicoTriggerEvento'])
            ->where('trigger', 'target|soglia')
            ->whereNumber('idTrigger');

        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
        // !! TARGET
    });
});

Route::group(['middleware' => ['auth:api', 'user']], function () {

    Route::group(['prefix' => 'Trax'], function () {

        Route::get('utenti', [TraxController::class, 'get_usable_user']);
        Route::get('utente/{id}/flotte', [TraxController::class, 'get_flotte_servizi'])->whereNumber('id');

        Route::get('rss', [RssController::class, 'get_all']);
        Route::get('utente', [TraxController::class, 'utenti_flotte']);
        // Route::get('utente', 'AuthController@listaUtenti');
        Route::post('posizione/{id}', [TraxController::class, 'mezzo_posizione'])->where('id', '[0-9]+');
        Route::post('posizione/{param}/{id}', [TraxController::class, 'flotta_posizione'])->where('id', '[0-9]+');
        Route::post('storico/{id}', [TraxController::class, 'storico'])->where('id', '[0-9]+');
        Route::post('parziale/{id?}', [TraxController::class, "parziale"])->where('id', '[0-9]+');
        Route::post('globale/{id?}', [TraxController::class, "globale"])->where('id', '[0-9]+');
        /** @deprecated */
        Route::post('tracciato/{id?}', [TraxController::class, "tracciato"])->where('id', '[0-9]+'); // Solo perchè matteo è gay

        Route::put('centrale_operativa', [TraxController::class, "update_devices_info"]);

        Route::post('{idServizio}/autisti', [TraxController::class, "autisti_per_mezzo"])->where('idServizio', '[0-9]+');
        Route::post('{idRadiocomando}/mezzi', [TraxController::class, "mezzi_per_autista"])->where('idRadiocomando', '[0-9]+');
    });

    Route::group(['prefix' => 'tipologia'], function () {
        Route::get('', [TipologiaController::class, 'get_all']);
        Route::get('{id}', [TipologiaController::class, 'get_id'])->where('id', '[0-9]+');
        Route::post('', [TipologiaController::class, 'create']);
        Route::put('{id}', [TipologiaController::class, 'create'])->where('id', '[0-9]+');
        Route::delete('{id}', [TipologiaController::class, 'delete'])->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'brand'], function () {
        Route::get('{params?}/{id?}', [BrandController::class, 'get_all'])->where('params', 'tipologia')->where('id', '[0-9]+');
        Route::get('{id}', [BrandController::class, 'get_id'])->where('id', '[0-9]+');
        Route::post('', [BrandController::class, 'create']);
        Route::put('{id}', [BrandController::class, 'create'])->where('id', '[0-9]+');
        Route::delete('{id}', [BrandController::class, 'delete'])->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'modello'], function () {
        Route::get('{params?}/{id?}', [ModelloController::class, 'get_all'])->where('params', 'tipologia')->where('id', '[0-9]+');
        Route::get('{id}', [ModelloController::class, 'get_id'])->where('id', '[0-9]+');
        Route::post('', [ModelloController::class, 'create']);
        Route::put('{id}', [ModelloController::class, 'create'])->where('id', '[0-9]+');
        Route::delete('{id}', [ModelloController::class, 'delete'])->where('id', '[0-9]+');
    });
});


Route::group(['middleware' => ['analyzerbe']], function () {
    Route::group(['prefix' => 'anagrafica'], function () {
        Route::get('{applicativo}', [AnagraficaController::class, 'get_per_applicativo'])->withoutMiddleware('auth:api');
    });
    Route::group(['prefix' => 'servizio'], function () {
        Route::get('{applicativo}/{scope?}', [ServizioController::class, 'get_per_applicativo'])
            ->where('scope', 'attivi|scaduti|futuri')->withoutMiddleware('auth:api');
    });
});
