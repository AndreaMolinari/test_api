<?php
use Illuminate\Support\Facades\Route;

Route::group(
    ['middleware' => ['auth:api', 'operatore', 'cors']], function () {

        Route::get('', 'Ticket\TicketController@getAll');
        Route::post('', 'Ticket\TicketController@new');
        
        // Route::group([
        //     'prefix' => 'ticket'
        // ], function(){
        //     // Route::get('/{id}', 'v3\AnagraficaController@getId')->where('id', '[0-9]+');
        //     // Route::get('/short', 'v3\AnagraficaController@short');
        //     // Route::get('/tipologia/{id}', 'v3\AnagraficaController@filterByTipologia')->where('id', '[0-9]+');
        //     // //Route::delete('/{id}', 'v3\AnagraficaController@delete')->where('id', '[0-9]+');
        //     // Route::post('', 'v3\AnagraficaController@insert');
        //     // Route::put('/{id}', 'v3\AnagraficaController@update')->where('id', '[0-9]+');
        // });
});