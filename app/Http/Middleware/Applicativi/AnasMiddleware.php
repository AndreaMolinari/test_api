<?php

namespace App\Http\Middleware\Applicativi;

use App\Models\TC_RolesTipologiaModel;
use App\Models\v5\Anagrafica;
use App\Models\v5\Servizio;
use App\Models\v5\Utente;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AnasMiddleware
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
        Servizio::addGlobalScope('anas_servizi', function (Builder $builder) {
            return $builder->whereHas('applicativi', function (Builder $b) {
                return $b->where('idTipologia', 127);
            });
        });
        
        Anagrafica::addGlobalScope('anas_anagrafica', function (Builder $builder) {
            return $builder->whereIn('TT_Anagrafica.id', Servizio::select('idAnagrafica')->attivi()->get()->pluck('idAnagrafica')->toArray());
        });

        Utente::addGlobalScope('anas_utenti', function (Builder $builder) {
            return $builder
                ->whereIn('idAnagrafica', Servizio::select('idAnagrafica')->attivi()->get()->pluck('idAnagrafica')->toArray())
                ->orWhereIn('idTipologia', TC_RolesTipologiaModel::select('idTipologia')->where('roles', '<', 5)->get()->pluck('idTipologia')->toArray());
        });
        return $next($request);
    }
}
