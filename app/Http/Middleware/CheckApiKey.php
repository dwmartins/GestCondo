<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('Origin');
        $apiKey = $request->header('X-Api-Key');

        if($origin === config('app.url')) {
            return $next($request);
        }

        if ($apiKey === config('app.api_key')) {
            return $next($request);
        }

        return response()->json(['error' => 'Acesso negado'], 401);
    }
}
