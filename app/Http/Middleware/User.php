<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class User
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if(Auth::check() && $user->getRoleLevel() <= 7){
            return $next($request);
        }

        return response(['message'=>'Non autorizzato'], 401)->header('Content-Type', 'text/plain');
    }

}
