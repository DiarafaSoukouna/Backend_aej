<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Observation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ObservationController extends Controller
{
    public function index(Request $request)
    {
        $observations = Observation::with(['microProjet', 'auteur']);

        if ($request->has('micro_projet_id') && !empty($request->micro_projet_id))
            $observations = $observations->where('micro_projet_id', $request->micro_projet_id);
        if ($request->has('auteur_id') && !empty($request->auteur_id))
            $observations = $observations->where('auteur_id', $request->auteur_id);

        $observationData = $observations->paginate($request->get('per_page', 20));

        return new JsonResponse([
            'message' => 'Observations retrieved successfully',
            'data' => $observationData,
            'pagination' => [
                'current_page' => $observationData->currentPage(),
                'last_page' => $observationData->lastPage(),
                'per_page' => $observationData->perPage(),
                'total' => $observationData->total(),
            ]
        ], 200);
    }

    public function show($id)
    {
        $observation = Observation::with(['microProjet', 'auteur'])->findOrFail($id);
        return new JsonResponse([
            'message' => 'Observation retrieved successfully',
            'data' => $observation
        ], 200);
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'auteur_id' => 'required|exists:personnels,id',
            'content' => 'required|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $observation = Observation::create($validation->validated());
            return new JsonResponse([
                'message' => 'Observation created successfully',
                'data' => $observation
            ], 201);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Observation creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $observation = Observation::findOrFail($id);
        
        $validation = Validator::make($request->all(), [
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'auteur_id' => 'required|exists:personnels,id',
            'content' => 'required|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $observation->update($validation->validated());
            return new JsonResponse([
                'message' => 'Observation updated successfully',
                'data' => $observation
            ], 200);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Observation update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $observation = Observation::findOrFail($id);
        try {
            $observation->delete();
            return response()->json(['message' => 'Observation deleted successfully'], 204);
        } catch (\Throwable $th) {
            return new JsonResponse([
                'message' => 'Observation deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
