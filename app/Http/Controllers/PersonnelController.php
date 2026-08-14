<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\MessageController;




class PersonnelController extends Controller
{
    public function index(): JsonResponse
    {
        $personnels = Personnel::with('role', 'fonction', 'agence', 'organisme')->get();
        return new JsonResponse(['Message' => 'Personnel list retrieved successfully', 'data' => $personnels], 200);
    }

    public function show($id): JsonResponse
    {
        $personnel = Personnel::with('role', 'agence', 'fonction', 'organisme')->find($id);
        if (!$personnel) {
            return new JsonResponse(['Message' => 'Personnel not found'], 404);
        }
        return new JsonResponse(['Message' => 'Personnel retrieved successfully', 'data' => $personnel], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:personnels',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'mot_de_passe' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'fonction_id' => 'required|exists:fonctions,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $data = $request->except('mot_de_passe');

            $motDePasseGenere = $request->mot_de_passe ?? 'AEJ' . now()->format('YmdHis');

            $personnel = Personnel::create(array_merge($data, [
                'mot_de_passe' => Hash::make($motDePasseGenere)
            ]));

            $sendMessage = new MessageController();
            $sendMessage->SendMailTo(new Request([
                'email'   => $personnel->email,
                'subject' => 'Bienvenue sur AEJ – Votre compte a été créé',
                'message' => "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>
                    Bienvenue sur AEJ 🎉
                </h2>

                <p style='color: #555; font-size: 15px;'>
                    Bonjour <strong>{$personnel->prenom} {$personnel->nom}</strong>,
                </p>

                <p style='color: #555; font-size: 15px;'>
                    Votre compte sur <strong>AEJ</strong> a été créé avec succès. 
                    Nous sommes ravis de vous compter parmi nos utilisateurs.
                </p>

                <div style='background-color: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; border-radius: 4px; margin: 20px 0;'>
                    <p style='color: #2e7d32; font-size: 15px; margin: 0;'>
                        <strong>Votre compte est maintenant actif.</strong><br><br>
                        C'est bien cet e-mail et ce mot de passe qui vous permettront de vous connecter :<br><br>
                        <strong>Email :</strong> {$personnel->email}<br>
                        <strong>Mot de passe :</strong> {$motDePasseGenere}
                    </p>
                </div>

                <p style='color: #555; font-size: 15px;'>
                    Pour des raisons de sécurité, vous devrez modifier votre mot de passe 
                    lors de votre première connexion.
                </p>

                <p style='color: #555; font-size: 15px;'>
                    Si vous avez besoin d'aide, n'hésitez pas à contacter notre équipe.
                </p>

                <div style='margin-top: 30px; padding-top: 15px; border-top: 1px solid #e0e0e0; text-align: center;'>
                    <p style='color: #888; font-size: 13px;'>
                        Cordialement,<br>
                        <strong style='color: #2c3e50;'>L'équipe AEJ</strong>
                    </p>
                </div>

            </div>
            "
            ]));

            return new JsonResponse([
                'message' => 'Personnel created successfully',
                'data' => $personnel
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $personnel = Personnel::find($id);
        if (!$personnel) {
            return new JsonResponse(['Message' => 'Personnel not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:personnels,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'role_id' => 'sometimes|required|exists:roles,id',
            'is_active' => 'boolean',
            'fonction_id' => 'sometimes|required|exists:fonctions,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $personnel->update($validation->validated());

            return new JsonResponse([
                'message' => 'Personnel updated successfully',
                'data' => $personnel
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request, $id): JsonResponse
    {
        $personnel = Personnel::find($id);
        if (!$personnel) {
            return new JsonResponse(['Message' => 'Personnel not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'mot_de_passe' => 'required|string|min:8|confirmed',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $personnel->update([
                'mot_de_passe' => Hash::make($request->mot_de_passe)
            ]);

            return new JsonResponse([
                'message' => 'Password updated successfully',
                'data' => $personnel
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function auth(Request $request): JsonResponse
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

        if (!$personnel || !Hash::check($request->mot_de_passe, $personnel->mot_de_passe)) {
            return new JsonResponse([
                'message' => 'Identifiants invalides'
            ], 401);
        }

        try {
            Auth::guard('web')->login($personnel);
            $request->session()->regenerate();
            $userId = $personnel->id;
            return new JsonResponse([
                'message' => 'Authentification réussie',
                'user_id' => $userId,
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de l\'authentification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'Message' => 'Logout successful'
        ], 200);
    }

    public function refresh(Request $request): JsonResponse
    {
        $request->session()->regenerate();

        return new JsonResponse([
            'message' => 'Session refreshed',
            'data' => $request->user(),
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Utilisateur récupéré avec succès',
            'data' => $request->user(),
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $personnel = Personnel::find($id);
        if (!$personnel) {
            return new JsonResponse(['Message' => 'Personnel not found'], 404);
        }

        try {
            $personnel->delete();
            return new JsonResponse(['Message' => 'Personnel deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
