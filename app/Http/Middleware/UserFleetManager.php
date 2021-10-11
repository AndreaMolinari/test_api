<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserFleetManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if(Auth::check() && $user->getRoleLevel() <= 6){
            return $next($request);
        }

        return response(['message'=>'Non autorizzato SU'], 401)->header('Content-Type', 'text/plain');
    }
}
