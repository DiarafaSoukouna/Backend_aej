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
    public function show($id)
    {
        $promoteur = Promoteur::findOrFail($id);
        return response()->json($promoteur);
    }

    public function filter(Request $request)
    {
        $query = Promoteur::query();

        $filters = [
            'tranche_age',
            'sexe_id',
            'agence_regionale_id',
            'secteur_activite_id',
            'sous_secteur_activite_id',
            'niveau_etude_id',
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
