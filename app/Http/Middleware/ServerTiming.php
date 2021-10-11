<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class ServerTiming {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        if (env('APP_DEBUG', false)) {
            $start = hrtime(true);
            /**@var Response */
            $response = $next($request);
            $end = hrtime(true);

            $response->header('Server-Timing', 'app;desc="Application";dur=' . round(($end - $start) / pow(10,6), 1));
            $response->header('Timing-Allow-Origin', '*');

            return $response;
        }

        return $next($request);
    }
}
