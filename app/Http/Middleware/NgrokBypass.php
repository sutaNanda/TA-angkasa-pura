<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NgrokBypass
{
    /**
     * Handle an incoming request.
     * Adds the ngrok-skip-browser-warning header to all responses
     * so that ngrok does not show the interstitial warning page for
     * sub-resources (CSS, JS, images) when testing via ngrok tunnel.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('ngrok-skip-browser-warning', 'true');
        return $response;
    }
}
