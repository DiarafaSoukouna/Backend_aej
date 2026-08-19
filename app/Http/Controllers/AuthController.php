<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\Token;
use App\Models\OtpCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Services\MailService;


class AuthController extends Controller
{

    public function login(Request $request, MailService $mailService): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'mot_de_passe' => 'required|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        $personnel = Personnel::with(['role', 'fonction', 'agence', 'organisme'])->where('email', $request->input('email'))->first();

        if (!$personnel || !Hash::check($request->mot_de_passe, $personnel->mot_de_passe)) {
            return new JsonResponse([
                'message' => 'Identifiants invalides'
            ], 401);
        }

        try {
            $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            OtpCode::where('personnel_id', $personnel->id)->where('used', false)->update(['used' => true]);

            OtpCode::create([
                'personnel_id' => $personnel->id,
                'code' => Hash::make($otpCode),
                'expires_at' => now()->addMinutes(15),
            ]);

            $mailService->sendOtpEmail($personnel->email, $otpCode);

            return new JsonResponse([
                'message' => 'Code OTP envoyé à votre adresse email.',
                'email' => $personnel->email,
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de l\'authentification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'code' => 'required|string|size:6',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $personnel = Personnel::where('email', $request->email)->first();

            if (!$personnel) {
                return new JsonResponse([
                    'message' => 'Identifiants invalides'
                ], 401);
            }

            $otpCodes = OtpCode::where('personnel_id', $personnel->id)->where('used', false)->where('expires_at', '>', now())->get();

            $validOtp = null;
            foreach ($otpCodes as $otp) {
                if (Hash::check($request->code, $otp->code)) {
                    $validOtp = $otp;
                    break;
                }
            }

            if (!$validOtp) {
                return new JsonResponse([
                    'message' => 'Code OTP invalide ou expiré.'
                ], 400);
            }

            $validOtp->markAsUsed();
            $token = $personnel->createToken('auth-token')->plainTextToken;
            $cookie = cookie('accessToken', $token, 60 * 24 * 30, '/', null, true, true, false, 'Lax'); // 30 jours, HTTPS, HTTP only, SameSite Lax

            return new JsonResponse([
                'message' => 'Code OTP validé, utilisateur connecté avec succès.',
                'user_id' => $personnel->id,
            ], 200)->withCookie($cookie);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la vérification du code OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? $request->cookie('accessToken') ?? $request->query('token');

        if ($token) {
            $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($tokenModel) {
                $tokenModel->delete();
            }
        }

        $cookie = cookie('accessToken', null, -1, '/', null, true, true, false, 'Lax');

        return response()->json([
            'Message' => 'Logout successful'
        ], 200)->withCookie($cookie);
    }

    public function refresh(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? $request->cookie('accessToken') ?? $request->query('token');

        if (!$token) {
            return new JsonResponse([
                'message' => 'Token manquant'
            ], 401);
        }

        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

        if (!$tokenModel) {
            return new JsonResponse([
                'message' => 'Token invalide'
            ], 401);
        }

        $user = $tokenModel->tokenable;

        if (!$user) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé'
            ], 401);
        }

        $tokenModel->delete();
        $newToken = $user->createToken('auth-token')->plainTextToken;
        $cookie = cookie('accessToken', $newToken, 60 * 24 * 30, '/', null, true, true, false, 'Lax');

        return new JsonResponse([
            'message' => 'Token refresh avec succès',
            'data' => $user->load(['role', 'fonction', 'agence', 'organisme']),
        ], 200)->withCookie($cookie);
    }

    public function me(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? $request->cookie('accessToken') ?? $request->query('token');

        if (!$token) {
            return new JsonResponse([
                'message' => 'Token manquant. Envoyez-le dans l\'en-tête Authorization: Bearer {token}, dans un cookie, ou comme paramètre ?token={token}'
            ], 401);
        }

        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

        if (!$tokenModel) {
            return new JsonResponse([
                'message' => 'Token invalide'
            ], 401);
        }

        $user = $tokenModel->tokenable;

        if (!$user) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé'
            ], 401);
        }

        return new JsonResponse([
            'message' => 'Utilisateur récupéré avec succès',
            'data' => $user->load(['role', 'fonction', 'agence', 'organisme']),
        ], 200);
    }
}
