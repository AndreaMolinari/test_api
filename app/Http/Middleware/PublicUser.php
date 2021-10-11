<?php

namespace App\Http\Middleware;

use Closure;

class PublicUser
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
