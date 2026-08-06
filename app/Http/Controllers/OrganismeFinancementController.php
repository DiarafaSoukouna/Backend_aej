<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrganismeFinancement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class OrganismeFinancementController extends Controller
{
    public function index(): JsonResponse
    {
        $organismes = OrganismeFinancement::with(['typeOrganisme', 'region'])->get();
        return new JsonResponse(['Message' => 'Organismes de financement list retrieved successfully', 'data' => $organismes], 200);
    }

    public function show($id): JsonResponse
    {
        $organisme = OrganismeFinancement::with(['typeOrganisme', 'region'])->find($id);
        if (!$organisme) {
            return new JsonResponse(['Message' => 'Organisme de financement not found'], 404);
        }
        return new JsonResponse(['Message' => 'Organisme de financement retrieved successfully', 'data' => $organisme], 200);
    }

    public function store(Request $request): JsonResponse
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
            'region_id' => 'nullable|exists:regions,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $organisme = OrganismeFinancement::create($validation->validated());

            return new JsonResponse([
                'message' => 'Organisme de financement created successfully',
                'data' => $organisme
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating organisme de financement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $organisme = OrganismeFinancement::find($id);
        if (!$organisme) {
            return new JsonResponse(['Message' => 'Organisme de financement not found'], 404);
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
            'region_id' => 'nullable|exists:regions,id',
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
                'message' => 'Organisme de financement updated successfully',
                'data' => $organisme
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating organisme de financement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $organisme = OrganismeFinancement::find($id);
        if (!$organisme) {
            return new JsonResponse(['Message' => 'Organisme de financement not found'], 404);
        }

        try {
            $organisme->delete();
            return new JsonResponse(['Message' => 'Organisme de financement deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting organisme de financement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByRegion($regionId): JsonResponse
    {
        $organismes = OrganismeFinancement::where('region_id', $regionId)->with(['typeOrganisme', 'region'])->get();
        return new JsonResponse(['Message' => 'Organismes de financement retrieved successfully', 'data' => $organismes], 200);
    }

    public function getByType($typeId): JsonResponse
    {
        $organismes = OrganismeFinancement::where('type', $typeId)->with(['typeOrganisme', 'region'])->get();
        return new JsonResponse(['Message' => 'Organismes de financement retrieved successfully', 'data' => $organismes], 200);
    }
}
