<?php

namespace App\Http\Middleware\Applicativi;

use Closure;
use Illuminate\Http\Request;

class BsdMiddleware
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

        if (!in_array($request->ip(), explode(',', env('ALLOWED_BSD_IP_ADDRESSES', '').','.env('DEBUG_IP_ADDRESSES', ''))))
            return response($request->ip(), 403);
        // dd($request->ip());
        return $next($request);
    }
}
