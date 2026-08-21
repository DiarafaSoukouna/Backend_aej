<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MicroProjet;
use Illuminate\Http\JsonResponse;

class MicroProjetController extends Controller
{
    public function index(Request $request)
    {
        $query = MicroProjet::with([
            'dispositif', 'organisme', 'guichet', 'secteur', 'commune', 
            'agence', 'agenceImputation', 'promoteur', 'workflowInstance'
        ]);

        $filters = [
            'dispositif_id', 'organisme_id', 'guichet_id', 'secteur_id', 'commune_id', 
            'agence_id', 'agence_imputation_id', 'promoteur_id', 
            'stade_projet', 'type_projet', 'statut'
        ];

        foreach ($filters as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('intitule', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 20);
        $microProjets = $query->paginate($perPage);

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

    public function show($id)
    {
        $microProjet = MicroProjet::with([
            'dispositif', 'organisme', 'guichet', 'secteur', 'commune', 
            'agence', 'agenceImputation', 'promoteur', 'workflowInstance'
        ])->findOrFail($id);
        
        return new JsonResponse([
            'message' => 'Micro Projet retrieved successfully',
            'data' => $microProjet
        ], 200);
    }
}