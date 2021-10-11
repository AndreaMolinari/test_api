<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;


class Operatore
{
    public function handle($request, Closure $next)
    {

        // ? Ricorda che 6 è il rivenditore. Deve avere controller diversi
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if(Auth::check() && $user->getRoleLevel() <= 3){
            return $next($request);
        }
        // elseif( Auth::check() && Auth::user()->getRoleLevel() == '99')
        // {
        //     dd($request);
        //     return $next($request);
        //     // return redirect('mlsListaAnagrafica');
        // }

        return response(['message'=>'Non autorizzato'], 401)->header('Content-Type', 'text/plain');
    }
}
