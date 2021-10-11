<?php

namespace App\Http\Middleware;

use App\Models\v5\Utente;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Impersonification
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
        /** @var Utente */
        $utente = Auth::user();
        if ($utente && $utente->getRoleLevel() <= 4) {

            if ($impersId = $request->header('loggedUsingId')) {
                $newUser = Utente::findOrFail($impersId);
                if ($utente->getRoleLevel() < $newUser->getRoleLevel()) {
                    Auth::setUser($newUser);
                }
            }
        }
        return $next($request);
    }
}
