<?php

namespace App\Http\Middleware;

use Closure;

class UserApi {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        exit('UserApi');
        return $next($request);
    }
}
