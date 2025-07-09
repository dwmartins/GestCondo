<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Handles user authentication and returns an access token.
     * 
     * Validates user credentials, cleans up old tokens, generates a new access token,
     * and returns the authentication response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws \Illuminate\Validation\ValidationException
     *
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $this->cleanTokens($request);
        $user = $request->user();
        $tokenExpiration = $request->rememberMe ? now()->addDays(30) : now()->addDay();
        $token = $user->createToken('auth_token', ['*'], $tokenExpiration)->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Validates the current authentication token and returns user information.
     * 
     * This endpoint checks if the provided bearer token is valid and returns
     * the authenticated user's data. Used for token validation in frontend apps.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function validateToken(Request $request)
    {
        return response()->json([
            'message' => 'Token válido',
            'user' => $request->user(),
            'is_valid' => true
        ]);
    }

    /**
     * Logs out the authenticated user by revoking the current access token.
     * 
     * Invalidates the bearer token used for the current request, effectively
     * terminating the user's session. Requires valid authentication.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    
        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    /**
     * Cleans up expired and unused tokens for the authenticated user.
     * 
     * Deletes all personal access tokens that either:
     * - Have never been used (last_used_at is NULL), or
     * - Have expired (expires_at is in the past)
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function cleanTokens(Request $request)
    {
        $user = $request->user();

        // Delete tokens that have never been used and have expired
        $user->tokens()
        ->where(function ($query) {
            $query->whereNull('last_used_at')
                ->orWhere('expires_at', '<', now());
        })->delete();
    }
}
