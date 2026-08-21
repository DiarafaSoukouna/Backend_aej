<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promoteur;
use Illuminate\Support\Facades\Schema;

class PromoteurController extends Controller
{
    public function index(Request $request)
    {
        $query = Promoteur::with([
            'sexe', 'agenceRegionale', 'secteurActivite', 'sousSecteurActivite',
            'niveauEtude', 'typePieceIdentite', 'paysNationalite', 'situationMatrimoniale',
            'typeSituationHandicap', 'lieuHabitation', 'personnel'
        ]);

        $filters = [
            'tranche_age', 'sexe_id', 'agenceregionale_id', 'secteuractivite_id',
            'soussecteuractivite_id', 'niveauetude_id', 'typepieceidentite_id', 'statut',
            'paysnationalite_id', 'situationmatrimoniale_id', 'typesituationhandicap_id'
        ];

        foreach ($filters as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $promoteurs = $query->paginate($perPage);

        return response()->json($promoteurs);
    }

    public function show(Request $request, $id)
    {
        $promoteur = Promoteur::with([
            'sexe', 'agenceRegionale', 'secteurActivite', 'sousSecteurActivite',
            'niveauEtude', 'typePieceIdentite', 'paysNationalite', 'situationMatrimoniale',
            'typeSituationHandicap', 'lieuHabitation', 'personnel',
            'microProjets' => function ($query) use ($request) {
                $filters = ['stade_projet', 'type_projet', 'statut'];
                foreach ($filters as $filter) {
                    if ($request->filled($filter)) $query->where($filter, $request->input($filter));
                }
            }
        ])->findOrFail($id);

        return response()->json($promoteur);
    }

    // public function exportCsv()
    // {
    //     $promoteurColumns = Schema::getColumnListing('promoteurs');
    //     $microProjetColumns = Schema::getColumnListing('micro_projets');
    //     $promoteurColumns = array_values(array_diff($promoteurColumns, ['statut']));
    //     $filename = 'promoteurs_' . now()->format('Y-m-d_H-i-s') . '.csv';

    //     $headers = [
    //         'Content-Type' => 'text/csv; charset=UTF-8',
    //         'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    //     ];

    //     return response()->stream(function () use ($promoteurColumns, $microProjetColumns) {
    //         $file = fopen('php://output', 'w');
    //         fwrite($file, "\xEF\xBB\xBF");

    //         $columns = array_merge(
    //             array_map(fn($col) => 'promoteur_' . $col, $promoteurColumns),
    //             array_map(fn($col) => 'micro_projet_' . $col, $microProjetColumns)
    //         );
    //         fputcsv($file, $columns, ';');

    //         Promoteur::with('microProjets')->chunk(500, function ($promoteurs) use ($file, $promoteurColumns, $microProjetColumns) {
    //             foreach ($promoteurs as $promoteur) {
    //                 $microProjet = $promoteur->microProjets->first();
    //                 $row = array_merge(
    //                     array_map(fn($col) => $promoteur->{$col}, $promoteurColumns),
    //                     array_map(fn($col) => $microProjet?->{$col} ?? null, $microProjetColumns)
    //                 );
    //                 fputcsv($file, $row, ';');
    //             }
    //         });

    //         fclose($file);
    //     }, 200, $headers);
    // }
}
