<?php

namespace App\Http\Middleware;

use Closure;

class AnalyzerBE
{
    public function handle($request, Closure $next)
    {
        if (in_array($request->ip(), explode(',', env('ALLOWED_ANALYZER_IP_ADDRESSES', '').','.env('DEBUG_IP_ADDRESSES', ''))))
            return $next($request);
        return response('', 403);
    }
}
