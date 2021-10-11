<?php

use App\Http\Controllers\v5\AnagraficaController;
use App\Http\Controllers\v5\AuthController;
use App\Http\Controllers\v5\ServizioController;
use App\Http\Middleware\InsomniaCookie;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->withoutMiddleware(['auth:v5', Impersonification::class])->middleware(InsomniaCookie::class);
Route::get('authCheck', [AuthController::class, 'authCheck']);
Route::delete('logout', [AuthController::class, 'logout']);

Route::prefix('servizi')->group(function () {
    Route::get('', [ServizioController::class, 'index']);
    Route::get('search', [ServizioController::class, 'search']);
    Route::prefix('{servizio}')->group(function () {
        Route::get('', [ServizioController::class, 'show']);
    });
});

Route::prefix('anagrafiche')->group(function () {
    Route::get('', [AnagraficaController::class, 'index']);
    Route::get('search', [AnagraficaController::class, 'search']);
    Route::prefix('{anagrafica}')->group(function () {
        Route::get('', [AnagraficaController::class, 'show']);
    });
});
