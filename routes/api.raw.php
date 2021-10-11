<?php

use App\Http\Controllers\Raw\ServizioController;
use App\Http\Middleware\Applicativi\BsdMiddleware;
use App\Http\Middleware\Applicativi\EspritMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('reverse_with', [ServizioController::class, 'reverse_with'])->middleware(BsdMiddleware::class);
Route::get('specifics', [ServizioController::class, 'specifics'])->middleware(EspritMiddleware::class);