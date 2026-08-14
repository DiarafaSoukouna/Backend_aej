<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Observation;
use Illuminate\Http\JsonResponse;

class ObservationController extends Controller
{
    public function index()
    {
        $observations = Observation::with(['microProjet', 'auteur'])->get();
        return new JsonResponse([
            'message' => 'Observations retrieved successfully',
            'data' => $observations
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
        $validated = $request->validate([
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'auteur_id' => 'required|exists:personnels,id',
            'content' => 'required|string',
        ]);

        $observation = Observation::create($validated);
        return new JsonResponse([
            'message' => 'Observation created successfully',
            'data' => $observation
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $observation = Observation::findOrFail($id);
        
        $validated = $request->validate([
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'auteur_id' => 'required|exists:personnels,id',
            'content' => 'required|string',
        ]);

        $observation->update($validated);
        return new JsonResponse([
            'message' => 'Observation updated successfully',
            'data' => $observation
        ], 200);
    }

    public function destroy($id)
    {
        $observation = Observation::findOrFail($id);
        $observation->delete();
        return new JsonResponse([
            'message' => 'Observation deleted successfully'
        ], 200);
    }
}
