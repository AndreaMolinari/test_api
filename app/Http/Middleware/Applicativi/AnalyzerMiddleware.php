<?php
namespace App\Http\Middleware\Applicativi;

use App\Http\Resources\Raw\Analyzer\AnagraficaResource;
use App\Http\Resources\Raw\Analyzer\ServizioResource;
use App\Models\TC_RolesTipologiaModel;
use App\Models\v5\Anagrafica;
use App\Models\v5\Servizio;
use App\Models\v5\Utente;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AnalyzerMiddleware
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
        Servizio::addGlobalScope('analyzer_servizi', function (Builder $builder) {
            return $builder->whereHas('applicativi', function (Builder $b) {
                return $b->where('idTipologia', 84);
            });
        });

        Anagrafica::addGlobalScope('analyzer_anagrafica', function (Builder $builder) {
            return $builder->whereIn('TT_Anagrafica.id', Servizio::select('idAnagrafica')->attivi()->get()->pluck('idAnagrafica')->toArray());
        });

        Utente::addGlobalScope('analyzer_utenti', function (Builder $builder) {
            return $builder
                ->whereIn('idAnagrafica', Servizio::select('idAnagrafica')->attivi()->get()->pluck('idAnagrafica')->toArray())
                ->orWhereIn('idTipologia', TC_RolesTipologiaModel::select('idTipologia')->where('roles', '<', 5)->get()->pluck('idTipologia')->toArray());
        });
        $request->attributes->add([
            'ServizioResource' => ServizioResource::class,
            'AnagraficaResource' => AnagraficaResource::class,
        ]);
        return $next($request);
    }
}
