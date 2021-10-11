<?php

namespace App\Http\Middleware;

use App\Http\Controllers\v4\SecretUserController;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAPIUser {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next) {
        if (SecretUserController::check_secret($request->header('secret') ?? '')) {
            Auth::loginUsingId(SecretUserController::get_userID($request->header('secret')));
            return $next($request);
        }
        return response()->json('Non autorizzato', 401);
    }
}
