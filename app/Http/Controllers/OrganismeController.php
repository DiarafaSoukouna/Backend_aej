<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisme;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class OrganismeController extends Controller
{
    public function index(): JsonResponse
    {
        $organismes = Organisme::all();
        return new JsonResponse(['Message' => 'Organismes list retrieved successfully', 'data' => $organismes], 200);
    }
    public function show($id): JsonResponse
    {
        $organisme = Organisme::find($id);
        if (!$organisme) {
            return new JsonResponse(['Message' => 'Organisme not found'], 404);
        }
        return new JsonResponse(['Message' => 'Organisme retrieved successfully', 'data' => $organisme], 200);
    }
    function store(Request $request) : JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:200',
            'sigle' => 'required|string|max:50',
            'type' => 'required|exists:type_organismes,id',
            'site_web' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
             $organisme = Organisme::create($validation->validated());

            return new JsonResponse([
                'message' => 'Organisme created successfully',
                'data' => $organisme
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating organisme',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id): JsonResponse
    {
        $organisme = Organisme::find($id);
        if (!$organisme) {
            return new JsonResponse(['Message' => 'Organisme not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:200',
            'sigle' => 'sometimes|required|string|max:50',
            'type' => 'sometimes|required|exists:type_organismes,id',
            'site_web' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:100',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $organisme->update($validation->validated());

            return new JsonResponse([
                'message' => 'Organisme updated successfully',
                'data' => $organisme
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating organisme',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id): JsonResponse
    {
        $organisme = Organisme::find($id);
        if (!$organisme) {
            return new JsonResponse(['Message' => 'Organisme not found'], 404);
        }

        try {
            $organisme->delete();
            return new JsonResponse(['Message' => 'Organisme deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting organisme',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
