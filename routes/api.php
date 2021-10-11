<?php

use App\Common\Helpers\TargetHelper;
use App\Common\Managers\PosizioniManager;
use App\Common\Managers\Redis\TargetManager;
use App\Events\InTargetEvent;
use App\Events\OutTargetEvent;
use App\Http\Controllers\{StatisticaController};
use App\Http\Controllers\v4\{AaTestController, AnagraficaController, ErrorController, FlottaController, ManutenzioneController, ServizioController, AuthController, AutistaController, RssController, SecretUserController, TraxController};
use App\Models\Targets\TT_AreaModel;
use App\Models\Targets\TT_StoricoEventoModel;
use App\Models\TT_ServizioModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

// Route::get('sim', function(Request $request){
//     $validated = $request->validate([
//         'base' => 'required',
//         'from' => 'required',
//         'to' => 'required'
//     ]);
//     $query = "INSERT INTO `TT_Sim` (`serial`, `idModello`, `idOperatore`, `created_at`, `updated_at`) VALUES ";
//     $q_part = [];
//     for ($i = $validated['from']; $i <= $validated['to']; $i++)
//     {
//         $tail = str_pad($i, 3, '0', STR_PAD_LEFT);
//         $q_part[] = "('".$validated['base'].$tail."', 5, 1, now(), now())";
//     }
//     return $query.implode(',', $q_part);
// });

Route::group(['prefix' => 'andrea', 'middleware' => ['auth:api', 'superAdmin']], function () {
    Route::get('rss', [RssController::class, 'get_all']);
    Route::post('rss', [RssController::class, 'set_all']);

    Route::get('import_autisti', [AutistaController::class, 'import_json']);

    Route::get('mlscad', [AaTestController::class, 'mls_scad']);

    Route::get('sandprod', [ManutenzioneController::class, 'sync_man_sandbox_prod']);

    Route::get('send_test_mail', [ManutenzioneController::class, 'send_test_mail']);

    Route::get('test_pre_mail', [ManutenzioneController::class, 'test_pre_mail']);

    Route::get('testLogs', [ErrorController::class, 'testLogs']);

    Route::get('updateMezzi', [ManutenzioneController::class, 'update_mezzi']);
    Route::get('setCurrentPosition', function () {
        return PosizioniManager::fetchAndUpdateLatests();
    });

    Route::post('import_vania/{idAnagrafica}', [ManutenzioneController::class, 'import_from_vania'])->where('idAnagrafica', '[0-9]+');

    Route::get('puzza', [AaTestController::class, 'testGiffi']);

    Route::post('reportMensile/{idUtente}', [ServizioController::class, 'getMensileUtente']);

    Route::post('servizio/search', [ServizioController::class, 'search']);
});

Route::group(['prefix' => 'stats'], function () {
    Route::get('', [StatisticaController::class, 'web_stats']);

    Route::group(['middleware' => ['auth:api', 'operatore']], function () {
        Route::get('tacho',         [StatisticaController::class, 'count_tacho']);
        Route::get('gps',           [StatisticaController::class, 'count_gps']);
        Route::get('mezzo',         [StatisticaController::class, 'count_mezzo']);
        Route::get('applicativo',   [StatisticaController::class, 'count_applicativo']);
        Route::get('rivenditori',   [StatisticaController::class, 'count_rivenditore']);
    });
});

Route::group(['middleware' => ['auth:api', 'superAdmin']], function () {
    Route::delete('anagrafica/{id}', [AnagraficaController::class, 'elimina'])->where('id', '[0-9]+');
    Route::delete('flotta/{id}', [FlottaController::class, 'delete'])->where('id', '[0-9]+');
    Route::delete('servizio/{id}', [ServizioController::class, 'delete'])->where('id', '[0-9]+');

    // Route::get('positions/{unitcode?}', 'v3\TraxController@getCurrentPositionAll')->where('unitcode', '[0-9]+')->middleware('superAdmin');
    Route::get('positions/{unitcode?}', [TraxController::class, 'getCurrentPositionAll'])->where('unitcode', '[0-9]+')->middleware('superAdmin');

    Route::group(['prefix' => 'secret'], function () {
        Route::get('', [SecretUserController::class, 'get_all']);
        Route::get('{id}', [SecretUserController::class, 'get_id'])->where('id', '[0-9]+');
        Route::post('', [SecretUserController::class, 'create']);
        Route::put('{id}', [SecretUserController::class, 'create'])->where('id', '[0-9]+');
        Route::delete('{id}', [SecretUserController::class, 'delete'])->where('id', '[0-9]+');
        Route::get('{param?}/{val?}', [SecretUserController::class, 'get_one'])->where('param', 'secret|user');
    });
});

Route::post('login', [AuthController::class, 'login'])->middleware(\App\Http\Middleware\InsomniaCookie::class);
Route::get('authCheck', [AuthController::class, 'authCheck'])->middleware(['auth:api']);
Route::delete('logout', [AuthController::class, 'logout']);

Route::group(['prefix' => 'test', 'middleware' => 'auth:api'], function () {
    Route::get('superAdmin', function () {
        return 'Bravo superAdmin';
    })->middleware('superAdmin');

    Route::get('admin', function () {
        return 'Bravo admin';
    })->middleware('admin');

    Route::get('operatore', function () {
        return 'Bravo operatore';
    })->middleware('operatore');

    Route::get('rivenditore', function () {
        return 'Bravo rivenditore';
    })->middleware('rivenditore');

    Route::get('superUser', function () {
        return 'Bravo superUser';
    })->middleware('superUser');

    Route::get('userFleetManager', function () {
        return 'Bravo userFleetManager';
    })->middleware('userFleetManager');

    Route::get('user', function () {
        return 'Bravo user';
    })->middleware('user');
});
