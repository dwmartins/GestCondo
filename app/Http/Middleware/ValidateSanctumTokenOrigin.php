<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSanctumTokenOrigin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            // if ($token->user_agent !== $request->userAgent() ||
            //     $token->ip_address !== $request->ip()) {
            //     return response()->json(['message' => 'Sessão inválida'], 403);
            // }
        }

        return $next($request);
    }
}
