<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SuperAdmin
{
    public function handle($request, Closure $next)
    {
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if(Auth::check() && $user->getRoleLevel() <= 1){
            return $next($request);
        }

        return response(['message'=>'Non autorizzato'], 401)->header('Content-Type', 'text/plain');
    }
}
