<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promoteur;

class PromoteurController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $promoteurs = Promoteur::paginate($perPage);

        return response()->json($promoteurs);
    }
    public function show(Request $request, $id)
    {
        $promoteur = Promoteur::with(['microProjets' => function ($query) use ($request) {
            $filters_micro_projets = [
                'stade_projet',
                'type_projet',
                'statut'
            ];

            foreach ($filters_micro_projets as $filter) {
                if ($request->filled($filter)) {
                    $query->where($filter, $request->input($filter));
                }
            }
        }])->findOrFail($id);

        return response()->json($promoteur);
    }

    public function filterWithProjects(Request $request)
    {
        $promoteurFilters = [
            'tranche_age',
            'sexe_id',
            'agenceregionale_id',
            'secteuractivite_id',
            'soussecteuractivite_id',
            'niveauetude_id',
            'typepieceidentite_id',
            'paysnationalite_id',
            'situationmatrimoniale_id',
            'handicap'
        ];

        $microProjetFilters = [
            'stade_projet',
            'type_projet',
            'statut'
        ];

        $query = Promoteur::query();

        foreach ($promoteurFilters as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $hasMicroProjetFilter = false;

        foreach ($microProjetFilters as $filter) {
            if ($request->filled($filter)) {
                $hasMicroProjetFilter = true;
                break;
            }
        }

        if ($hasMicroProjetFilter) {
            $query->whereHas('microProjets', function ($microProjetQuery) use ($request, $microProjetFilters) {
                foreach ($microProjetFilters as $filter) {
                    if ($request->filled($filter)) {
                        $microProjetQuery->where($filter, $request->input($filter));
                    }
                }
            });
        }

        $query->with(['microProjets' => function ($microProjetQuery) use ($request, $microProjetFilters, $hasMicroProjetFilter) {
            if ($hasMicroProjetFilter) {
                foreach ($microProjetFilters as $filter) {
                    if ($request->filled($filter)) {
                        $microProjetQuery->where($filter, $request->input($filter));
                    }
                }
            }
        }]);

        $perPage = $request->get('per_page', 15);

        return response()->json($query->paginate($perPage));
    }

    public function filter(Request $request)
    {
        $query = Promoteur::query();

        $filters = [
            'tranche_age',
            'sexe_id',
            'agenceregionale_id',
            'secteuractivite_id',
            'soussecteuractivite_id',
            'niveauetude_id',
            'statut',
            'typepieceidentite_id',
            'paysnationalite_id',
            'situationmatrimoniale_id',
            'handicap'
        ];

        foreach ($filters as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $perPage = $request->get('per_page', 15);

        return response()->json($query->paginate($perPage));
    }
}
