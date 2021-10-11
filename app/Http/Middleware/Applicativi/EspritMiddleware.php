<?php
namespace App\Http\Middleware\Applicativi;

use App\Models\v5\Componente;
use Illuminate\Http\Request;
use Closure;

class EspritMiddleware
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
        if (!in_array($request->ip(), explode(',', env('ALLOWED_ESPRIT_IP_ADDRESSES', '').','.env('DEBUG_IP_ADDRESSES', '')))) {
            return response($request->ip(), 403);
        }
        Componente::addGlobalScope('esprit_xtrax', fn ($builder) => $builder->byBrand(4));
        return $next($request);
    }
}
