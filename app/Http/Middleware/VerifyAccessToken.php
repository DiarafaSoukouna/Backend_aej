<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\PersonalAccessToken;

class VerifyAccessToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Récupérer le token depuis différentes sources
        $token = $request->bearerToken() ?? $request->cookie('accessToken') ?? $request->query('token');

        if (!$token) {
            return new JsonResponse([
                'message' => 'Token manquant. Authentification requise.'
            ], 401);
        }

        // Trouver le token dans la base de données
        $tokenModel = PersonalAccessToken::findToken($token);

        if (!$tokenModel) {
            return new JsonResponse([
                'message' => 'Token invalide ou expiré.'
            ], 401);
        }

        // Vérifier si le token est expiré
        if ($tokenModel->expires_at && $tokenModel->expires_at->isPast()) {
            return new JsonResponse([
                'message' => 'Token expiré.'
            ], 401);
        }

        // Récupérer l'utilisateur associé au token
        $user = $tokenModel->tokenable;

        if (!$user) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé.'
            ], 401);
        }

        // Attacher l'utilisateur à la requête
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Stocker le token dans la requête pour utilisation ultérieure
        $request->attributes->set('sanctum_token', $tokenModel);

        return $next($request);
    }
}