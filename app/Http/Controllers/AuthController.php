<?php

namespace App\Http\Controllers;

use App\Models\Condominium;
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

        $user = $request->user();

        if($user->role !== 'suporte') {
            $condominium = $user->condominiums()
                ->where('is_active', true)
                ->first();

            if(!$condominium) {
                return response()->json([
                    'message' => 'Usuário inativo. Entre em contato com o administrador.'
                ], 403); 
            }
        }

        if (!$user->account_status) {
            return response()->json([
                'message' => 'Usuário inativo. Entre em contato com o administrador.'
            ], 403);
        }

        $this->cleanTokens($request);

        $tokenExpiration = $request->rememberMe ? now()->addDays(30) : now()->addDay();
        $token = $user->createToken('auth_token', ['*'], $tokenExpiration)->plainTextToken;

        $tokenModel = $user->tokens()->latest()->first();
        $tokenModel->user_agent = $request->userAgent();
        $tokenModel->ip_address = $request->ip();
        $tokenModel->save();

        $user->updateLastLogin();

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
        $user = $request->user();

        if($user->role !== 'suporte') {
            $condominium = $user->condominiums()
                ->where('is_active', true)
                ->first();

            if(!$condominium) {
                return response()->json([
                    'message' => 'Usuário inativo. Entre em contato com o administrador.'
                ], 403); 
            }
        }

        if (!$user || !$user->account_status) {
            return response()->json([
                'message' => 'Usuário inativo ou token inválido.',
                'is_valid' => false
            ], 403);
        }

        $LastViewedCondominiumId = $this->assignDefaultCondominiumIfMissing($request);

        return response()->json([
            'message' => 'Token válido',
            'user' => $request->user(),
            'is_valid' => true,
            'lastViewedCondominiumId' => $LastViewedCondominiumId
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
     * Update the last viewed condominium ID for the authenticated user.
     *
     * This method receives a condominium_id from the request, validates it,
     * and updates the authenticated user's last_viewed_condominium_id field.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     */
    public function updateLastViewedCondominium(Request $request)
    {
        $user = $request->user();
        $condominiumId = $request->input('condominium_id');

        if(!$condominiumId) {
            return response()->json([
                'message' => 'O ID do condomínio é obrigatório.'
            ], 422);
        }

        $user->updateLastViewedCondominium($condominiumId);

        return response()->json([
            'message' => 'Condomínio selecionado com sucesso.'
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

    /**
     * Ensures that the authenticated user has a valid `last_viewed_condominium_id`.
     *
     * This method checks whether the authenticated user has a `last_viewed_condominium_id` assigned.
     * If not, it attempts to assign one using the following logic:
     * 
     * 1. If the `X-Condominium-Id` header is present and references an existing condominium, it is used.
     * 2. Otherwise, the first active condominium (based on `is_active = true`) is assigned.
     *
     * This is useful to ensure the user context always has a default condominium when one hasn't been manually selected.
     *
     * @param \Illuminate\Http\Request $request
     */
    private function assignDefaultCondominiumIfMissing(Request $request) {
        $user = $request->user();

        if(!$user->last_viewed_condominium_id) {
            if ($user->role === 'suporte') {
                $condominiumIdFromHeader = $request->header('X-Condominium-Id');

                if ($condominiumIdFromHeader && Condominium::find($condominiumIdFromHeader)) {
                    $user->updateLastViewedCondominium($condominiumIdFromHeader);
                } else {
                    $firstCondominium = Condominium::first();

                    if ($firstCondominium) {
                        $user->updateLastViewedCondominium($firstCondominium->id);
                    }
                }
            } else {
                $firstUserCondominium = $user->condominiums()
                    ->where('is_active', true)
                    ->first();

                if ($firstUserCondominium) {
                    $user->updateLastViewedCondominium($firstUserCondominium->id);
                }
            }
        }

        return $user->last_viewed_condominium_id;
    }
}
