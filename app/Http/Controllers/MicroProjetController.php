<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MicroProjet;
use Illuminate\Http\JsonResponse;

class MicroProjetController extends Controller
{
 public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);

        $microProjets = MicroProjet::paginate($perPage);

        return new JsonResponse([
            'message' => 'Micro Projets retrieved successfully',
            'data' => $microProjets->items(),
            'pagination' => [
                'current_page' => $microProjets->currentPage(),
                'per_page' => $microProjets->perPage(),
                'total' => $microProjets->total(),
                'last_page' => $microProjets->lastPage(),
                'from' => $microProjets->firstItem(),
                'to' => $microProjets->lastItem(),
            ],
        ], 200);
    }

    public function filter(Request $request)
    {
        $query = MicroProjet::query();

        $filters = [
        'stade_projet',
        'type_projet',
        'statut'
        ];

        foreach ($filters as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $perPage = $request->get('per_page', 20);
        $microProjets = $query->paginate($perPage);

        return new JsonResponse([
            'message' => 'Micro Projets filtered successfully',
            'data' => $microProjets->items(),
            'pagination' => [
                'current_page' => $microProjets->currentPage(),
                'per_page' => $microProjets->perPage(),
                'total' => $microProjets->total(),
                'last_page' => $microProjets->lastPage(),
                'from' => $microProjets->firstItem(),
                'to' => $microProjets->lastItem(),
            ],
        ], 200);
    }

    public function show($id)
    {
        $microProjet = MicroProjet::with(['dispositif', 'organisme', 'guichet', 'secteur', 'commune', 'agence', 'agenceImputation', 'promoteur'])->findOrFail($id);
        return new JsonResponse([
            'message' => 'Micro Projet retrieved successfully',
            'data' => $microProjet
        ], 200);
    }
}