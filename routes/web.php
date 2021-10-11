<?php

use App\Http\Controllers\v4\ServizioController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('api/documentation');
});

Route::get('/test', [ServizioController::class, 'test']);

// Route::group(['prefix' => 'auth'], function () {
//     Route::post('login', 'AuthController@login');

//     Route::post('signup', 'AuthController@signup');
// });