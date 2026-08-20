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
use App\Services\WhatsAppService;


class AuthController extends Controller
{

    private function initCookies($personnel)
    {
        $accessToken = $personnel->createToken('access-token', ['expires_at' => now()->addMinutes(30)])->plainTextToken;
        $refreshToken = $personnel->createToken('refresh-token', ['expires_at' => now()->addDays(30)])->plainTextToken;
        $accessCookie = cookie('accessToken', $accessToken, 30, '/', null, true, true, false, 'Lax');
        $refreshCookie = cookie('refreshToken', $refreshToken, 60 * 24 * 30, '/', null, true, true, false, 'Lax');

        return ['accessToken' => $accessCookie, 'refreshToken' => $refreshCookie];
    }

    public function login(Request $request): JsonResponse
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

        $personnel = Personnel::where('email', $request->input('email'))->first();
        $configOTP = true;

        if (!$personnel || !Hash::check($request->mot_de_passe, $personnel->mot_de_passe)) {
            return new JsonResponse([
                'message' => 'Identifiants invalides'
            ], 401);
        }

        if (!$configOTP) {
            $cookies = $this->initCookies($personnel);

            return new JsonResponse([
                'message' => 'Utilisateur connecté avec succès.',
                'user_id' => $personnel->id
            ], 200)
                ->withCookie($cookies['accessToken'])
                ->withCookie($cookies['refreshToken']);
        }

        return new JsonResponse([
            'message' => 'Identifiants valides. Veuillez envoyer le code OTP.',
            'user_id' => $personnel->id,
            'has_phone' => !empty($personnel->telephone),
            'otp_required' => $configOTP,
        ], 200);
    }

    public function sendOtp(Request $request, MailService $mailService, WhatsAppService $whatsappService): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'mode' => 'required|in:MAIL,WHATSAPP',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        $personnel = Personnel::where('email', $request->input('email'))->first();

        if (!$personnel) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        try {
            $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $mode = $request->input('mode', 'MAIL');

            OtpCode::where('personnel_id', $personnel->id)->where('used', false)->update(['used' => true]);
            OtpCode::create([
                'personnel_id' => $personnel->id,
                'code' => Hash::make($otpCode),
                'mode' => $mode,
                'expires_at' => now()->addMinutes(15),
            ]);

            if ($mode === 'WHATSAPP') {
                if (!$whatsappService->isConfigured()) {
                    return new JsonResponse([
                        'message' => 'Service WhatsApp non configuré. Veuillez contacter l\'administrateur.'
                    ], 500);
                }

                if (empty($personnel->telephone)) {
                    return new JsonResponse([
                        'message' => 'Numéro de téléphone non configuré pour ce compte.'
                    ], 400);
                }

                $whatsappService->sendOtp($personnel->telephone, $otpCode);

                return new JsonResponse([
                    'message' => 'Code OTP envoyé via WhatsApp.',
                    'user_id' => $personnel->id,
                    'mode' => 'WHATSAPP',
                ], 200);
            }

            $mailService->sendOtpEmail($personnel->email, $otpCode);

            return new JsonResponse([
                'message' => 'Code OTP envoyé à votre adresse email.',
                'user_id' => $personnel->id,
                'mode' => 'MAIL',
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de l\'envoi du code OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'code' => 'required|string|size:6',
            'mode' => 'required|in:MAIL,WHATSAPP',
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

            $mode = $request->input('mode');
            $otpCodes = OtpCode::where('personnel_id', $personnel->id)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->where('mode', $mode)
                ->get();

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
            $cookies = $this->initCookies($personnel);

            return new JsonResponse([
                'message' => 'Code OTP validé, utilisateur connecté avec succès.',
                'user_id' => $personnel->id
            ], 200)
                ->withCookie($cookies['accessToken'])
                ->withCookie($cookies['refreshToken']);
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
            if ($tokenModel) $tokenModel->delete();
        }

        $accessToken = cookie('accessToken', null, -1, '/', null, true, true, false, 'Lax');
        $refreshCookie = cookie('refreshToken', null, -1, '/', null, true, true, false, 'Lax');

        return response()->json([
            'Message' => 'Logout successful'
        ], 200)
            ->withCookie($accessToken)
            ->withCookie($refreshCookie);
    }

    public function refresh(Request $request): JsonResponse
    {
        $token = $request->bearerToken() ?? $request->cookie('refreshToken') ?? $request->query('token');

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

        if ($tokenModel->expires_at && $tokenModel->expires_at->isPast()) {
            return new JsonResponse([
                'message' => 'Token expiré'
            ], 401);
        }

        $personnel = $tokenModel->tokenable;

        if (!$personnel) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé'
            ], 401);
        }

        $tokenModel->delete();
        $cookies = $this->initCookies($personnel);

        return new JsonResponse([
            'message' => 'Token refresh avec succès',
            'user_id' => $personnel->id,
        ], 200)
            ->withCookie($cookies['accessToken'])
            ->withCookie($cookies['refreshToken']);
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

        $personnel = $tokenModel->tokenable;

        if (!$personnel) {
            return new JsonResponse([
                'message' => 'Utilisateur non trouvé'
            ], 401);
        }

        return new JsonResponse([
            'message' => 'Utilisateur récupéré avec succès',
            'data' => $personnel->load(['role', 'fonction', 'agence', 'organisme']),
            'permissions' => $personnel->getAllPermissions(),
        ], 200);
    }
}
