<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Personnel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;


class PersonnelController extends Controller
{
    public function index() : JsonResponse
    {
        $personnels = Personnel::all();
        return new JsonResponse(['Message' => 'Personnel list retrieved successfully', 'data' => $personnels], 200);
    }
    public function show($id) : JsonResponse
    {
        $personnel = Personnel::find($id);
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
        'mot_de_passe' => 'required|string|min:8',
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
            'mot_de_passe' => Hash::make($request->mot_de_passe)
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
public function updatePassword(Request $request, $id) : JsonResponse
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
        $token = $personnel->createToken('auth_token')->plainTextToken;

        return new JsonResponse([
            'message' => 'Authentification réussie',
            'data' => $personnel,
            'token' => $token
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
   $request->user()->currentAccessToken()->delete();

    return response()->json([
        'Message' => 'Logout successful'
    ], 200);
}
public function destroy($id) : JsonResponse
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
