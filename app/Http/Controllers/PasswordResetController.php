<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\OtpCode;
use App\Models\Personnel;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request, MailService $mailService): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        $personnel = Personnel::where('email', $request->email)->first();

        if (!$personnel) {
            return new JsonResponse([
                'message' => 'Si cet email existe, un lien de réinitialisation sera envoyé.'
            ], 200);
        }

        try {
            Token::where('personnel_id', $personnel->id)
                ->where('type', 'RESET')
                ->where('used', false)
                ->update(['used' => true]);

            $token = $this->createToken($personnel->id, 'RESET');
            $resetUrl = config('app.url') . '/reset-password?token=' . $token;
            $mailService->sendPasswordResetEmail($personnel->email, $resetUrl);

            return new JsonResponse([
                'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email.'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la demande de réinitialisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $tokens = Token::where('type', 'RESET')
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->get();

            $validToken = null;
            foreach ($tokens as $t) {
                if (Hash::check($request->token, $t->token)) {
                    $validToken = $t;
                    break;
                }
            }

            if (!$validToken) {
                return new JsonResponse([
                    'message' => 'Token invalide ou expiré.'
                ], 400);
            }

            $personnel = Personnel::find($validToken->personnel_id);
            $personnel->update([
                'mot_de_passe' => Hash::make($request->password)
            ]);

            $validToken->markAsUsed();

            return new JsonResponse([
                'message' => 'Mot de passe réinitialisé avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la réinitialisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function setupPassword(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $tokens = Token::where('type', 'SETUP')
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->get();

            $validToken = null;
            foreach ($tokens as $t) {
                if (Hash::check($request->token, $t->token)) {
                    $validToken = $t;
                    break;
                }
            }

            if (!$validToken) {
                return new JsonResponse([
                    'message' => 'Token invalide ou expiré.'
                ], 400);
            }

            $personnel = Personnel::find($validToken->personnel_id);
            $personnel->update([
                'mot_de_passe' => Hash::make($request->password)
            ]);

            $validToken->markAsUsed();

            return new JsonResponse([
                'message' => 'Mot de passe défini avec succès. Vous pouvez maintenant vous connecter.'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la définition du mot de passe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function createToken(int $personnelId, string $type): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $hashedToken = Hash::make($plainToken);

        Token::create([
            'personnel_id' => $personnelId,
            'token' => $hashedToken,
            'type' => $type,
            'expires_at' => now()->addHours(1),
            'created_at' => now(),
        ]);

        return $plainToken;
    }
}