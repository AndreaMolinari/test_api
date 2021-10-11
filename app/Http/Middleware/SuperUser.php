<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SuperUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    //! Utente che accede a tutto trax
    public function handle($request, Closure $next)
    {
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if(Auth::check() && $user->getRoleLevel() <= 5){
            return $next($request);
        }

        return response(['message'=>'Non autorizzato SU'], 401)->header('Content-Type', 'text/plain');
    }
}
