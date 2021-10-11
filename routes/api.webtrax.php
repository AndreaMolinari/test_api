<?php

use App\Http\Controllers\v5\AuthController;
use App\Http\Controllers\v5\ServizioController;
use App\Http\Middleware\InsomniaCookie;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->withoutMiddleware(['auth:v5', Impersonification::class])->middleware(InsomniaCookie::class);
Route::get('authCheck', [AuthController::class, 'authCheck']);
Route::delete('logout', [AuthController::class, 'logout']);

Route::prefix('servizi')->group(function () {
    // Route::post('search', [ServizioController::class, 'search']);
    // Route::put('{servizio}', [ServizioController::class, 'update']); // TODO forse aggiungo una nota al servizio
    Route::get('', [ServizioController::class, 'index']);
    Route::get('search', [ServizioController::class, 'search']);
    Route::prefix('{servizio}')->group(function () {
        Route::get('', [ServizioController::class, 'show']);
        Route::post('accise', [ServizioController::class, 'show']); // ? ricerca accise da azure per un servizio specifico
    });
    Route::post('accise', [ServizioController::class, 'show']); // ? ricerca accise da azure per tutti i miei servizi
});
