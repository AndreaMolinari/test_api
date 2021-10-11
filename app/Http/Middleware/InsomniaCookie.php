<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Cookie;

class InsomniaCookie {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        /**@var Response */
        $response = $next($request);
        if (str_contains(strtolower($request->userAgent()), 'insomnia')) {
            $data = json_decode($response->getContent());
            $expires = $data->expires_at ?? 0;
            if (isset($data->access_token))
                $response->cookie(new Cookie('access_token', $data->access_token, $expires));
            if (isset($data->refresh_token))
                $response->cookie(new Cookie('refresh_token', $data->refresh_token, $expires));
        }

        return $response;
    }
}
