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

    public function store(Request $request, MailService $mailService): JsonResponse
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

            $personnel = Personnel::create(array_merge($data, [
                'mot_de_passe' => Hash::make('TEMP_PASSWORD_' . now()->format('YmdHis'))
            ]));

            $mailService->sendWelcomeEmail($personnel);
            $plainToken = bin2hex(random_bytes(32));
            $hashedToken = Hash::make($plainToken);

            Token::create([
                'personnel_id' => $personnel->id,
                'token' => $hashedToken,
                'type' => 'SETUP',
                'expires_at' => now()->addHours(24), // Valide 24h
                'created_at' => now(),
            ]);

            $setupUrl = config('mail.url') . '/setup-password?token=' . $plainToken;
            $mailService->sendSetupEmail($personnel, $setupUrl);

            return new JsonResponse([
                'message' => 'Personnel created successfully. Un email de configuration a été envoyé.',
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
