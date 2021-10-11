<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;


class Admin
{
    public function handle($request, Closure $next)
    {
        // exit( json_encode(Auth::check()) );
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if(Auth::check() && $user->getRoleLevel() <= 2){
            return $next($request);
        }

        return response(['message'=>'Non autorizzato'], 401)->header('Content-Type', 'text/plain');
    }
}
