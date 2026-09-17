<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promoteur;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;

class PromoteurController extends Controller
{
    // -------------------------------------------------------------------------
    // Helpers privés — Stats calculées (nombre_emplois, montant_rembourse, taux_impaye)
    // -------------------------------------------------------------------------

    private function applyStats($query)
    {
        return $query
            ->withCount('embauches')
            ->withSum('remboursements', 'montant_paye')
            ->withSum('remboursements', 'montant_echu')
            ->withSum('remboursements', 'montant_impaye');
    }

    private function transformStats($collection)
    {
        return $collection->transform(function ($promoteur) {
            $totalEchu   = (float) ($promoteur->remboursements_montant_echu_sum   ?? 0);
            $totalImpaye = (float) ($promoteur->remboursements_montant_impaye_sum ?? 0);

            $promoteur->nombre_emplois    = (int)   ($promoteur->embauches_count                    ?? 0);
            $promoteur->montant_rembourse = (float) ($promoteur->remboursements_montant_paye_sum    ?? 0);
            $promoteur->taux_impaye       = $totalEchu > 0
                ? round(($totalImpaye / $totalEchu) * 100, 2)
                : 0.0;

            unset(
                $promoteur->embauches_count,
                $promoteur->remboursements_montant_paye_sum,
                $promoteur->remboursements_montant_echu_sum,
                $promoteur->remboursements_montant_impaye_sum
            );

            return $promoteur;
        });
    }

    // -------------------------------------------------------------------------
    // Endpoints
    // -------------------------------------------------------------------------


    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $query = $this->applyStats(Promoteur::with('microProjets'));

        $promoteurs = $query->paginate($perPage);

        $this->transformStats($promoteurs->getCollection());

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

      
        $promoteur->loadCount('embauches')
                  ->loadSum('remboursements', 'montant_paye')
                  ->loadSum('remboursements', 'montant_echu')
                  ->loadSum('remboursements', 'montant_impaye');

        $this->transformStats(collect([$promoteur]));

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

        $this->applyStats($query);

        $perPage = $request->get('per_page', 15);

        $paginator = $query->paginate($perPage);

        $this->transformStats($paginator->getCollection());

        return response()->json($paginator);
    }

    public function filter(Request $request)
    {
        $query = Promoteur::query();

        $filters = [
            'tranche_age',
            'sexe_id',
            'lieuhabitation_id',
            'agenceregionale_id',
            'secteuractivite_id',
            'soussecteuractivite_id',
            'niveauetude_id',
            'statut',
            'typepieceidentite_id',
            'paysnationalite_id',
            'situationmatrimoniale_id',
            'typesituationhandicap_id',
            'handicap'
        ];

        foreach ($filters as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $this->applyStats($query);

        $promoteurs = $query->paginate($request->get('per_page', 15));

        // Les référentiels ne sont pas dans ce projet : on les récupère via leurs APIs.
        $apiBase = 'https://apis.aej-ci.net/public/api/aej/';

        $references = [
            'sexe' => [
                'url' => $apiBase . 'sexes',
                'field' => 'libelle'
            ],
            'lieuhabitation' => [
                'url' => $apiBase . 'lieu-habitations',
                'field' => 'nom'
            ],
            'typepieceidentite' => [
                'url' => $apiBase . 'types-pieces-identites',
                'field' => 'libelle'
            ],
            'niveauetude' => [
                'url' => $apiBase . 'niveaux-etudes',
                'field' => 'libelle'
            ],
            'paysnationalite' => [
                'url' => $apiBase . 'pays',
                'field' => 'nom'
            ],
            'typesituationhandicap' => [
                'url' => $apiBase . 'situations-handicaps',
                'field' => 'libelle'
            ],
            'situationmatrimoniale' => [
                'url' => $apiBase . 'situations-matrimoniales',
                'field' => 'libelle'
            ],
            'secteuractivite' => [
                'url' => $apiBase . 'secteurs',
                'field' => 'nom'
            ],
            'soussecteuractivite' => [
                'url' => $apiBase . 'sous-secteurs',
                'field' => 'nom'
            ],
            'agenceregionale' => [
                'url' => $apiBase . 'agences-regionales',
                'field' => 'nom'
            ],
        ];

        $maps = [];

        foreach ($references as $key => $reference) {
            $response = Http::timeout(10)->get($reference['url']);

            if ($response->successful()) {
                $data = $response->json();

                // Gestion d'une réponse paginée ou d'un tableau direct.
                $items = $data['data'] ?? $data;

                $maps[$key] = collect($items)->mapWithKeys(function ($item) use ($reference) {
                    return [
                        $item['id'] => $item[$reference['field']] ?? null
                    ];
                })->toArray();
            } else {
                $maps[$key] = [];
            }
        }

        $promoteurs->getCollection()->transform(function ($promoteur) use ($maps) {
            // Référentiels externes
            $promoteur->sexe                = $maps['sexe'][$promoteur->sexe_id]                               ?? null;
            $promoteur->lieuhabitation      = $maps['lieuhabitation'][$promoteur->lieuhabitation_id]           ?? null;
            $promoteur->typepieceidentite   = $maps['typepieceidentite'][$promoteur->typepieceidentite_id]     ?? null;
            $promoteur->niveauetude         = $maps['niveauetude'][$promoteur->niveauetude_id]                 ?? null;
            $promoteur->paysnationalite     = $maps['paysnationalite'][$promoteur->paysnationalite_id]         ?? null;
            $promoteur->typesituationhandicap = $maps['typesituationhandicap'][$promoteur->typesituationhandicap_id] ?? null;
            $promoteur->situationmatrimoniale = $maps['situationmatrimoniale'][$promoteur->situationmatrimoniale_id] ?? null;
            $promoteur->secteuractivite     = $maps['secteuractivite'][$promoteur->secteuractivite_id]         ?? null;
            $promoteur->soussecteuractivite = $maps['soussecteuractivite'][$promoteur->soussecteuractivite_id] ?? null;
            $promoteur->agenceregionale     = $maps['agenceregionale'][$promoteur->agenceregionale_id]        ?? null;

            unset(
                $promoteur->sexe_id,
                $promoteur->lieuhabitation_id,
                $promoteur->typepieceidentite_id,
                $promoteur->niveauetude_id,
                $promoteur->paysnationalite_id,
                $promoteur->typesituationhandicap_id,
                $promoteur->situationmatrimoniale_id,
                $promoteur->secteuractivite_id,
                $promoteur->soussecteuractivite_id,
                $promoteur->agenceregionale_id
            );

            // Stats calculées
            $totalEchu   = (float) ($promoteur->remboursements_montant_echu_sum   ?? 0);
            $totalImpaye = (float) ($promoteur->remboursements_montant_impaye_sum ?? 0);

            $promoteur->nombre_emplois    = (int)   ($promoteur->embauches_count                 ?? 0);
            $promoteur->montant_rembourse = (float) ($promoteur->remboursements_montant_paye_sum ?? 0);
            $promoteur->taux_impaye       = $totalEchu > 0
                ? round(($totalImpaye / $totalEchu) * 100, 2)
                : 0.0;

            unset(
                $promoteur->embauches_count,
                $promoteur->remboursements_montant_paye_sum,
                $promoteur->remboursements_montant_echu_sum,
                $promoteur->remboursements_montant_impaye_sum
            );

            return $promoteur;
        });

        return response()->json($promoteurs);
    }
    public function exportCsv()
{
    $promoteurColumns = Schema::getColumnListing('promoteurs');
    $microProjetColumns = Schema::getColumnListing('micro_projets');

    // On exclut uniquement le statut du promoteur
    $promoteurColumns = array_values(
        array_diff($promoteurColumns, ['statut'])
    );

    $filename = 'promoteurs_microprojets_' . now()->format('Y-m-d_H-i-s') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    return response()->stream(function () use (
        $promoteurColumns,
        $microProjetColumns
    ) {

        $file = fopen('php://output', 'w');

        // BOM UTF-8 pour Excel
        fwrite($file, "\xEF\xBB\xBF");

        // Colonnes du CSV
        $columns = [];

        foreach ($promoteurColumns as $column) {
            $columns[] = 'promoteur_' . $column;
        }

        foreach ($microProjetColumns as $column) {
            $columns[] = 'micro_projet_' . $column;
        }

        fputcsv($file, $columns, ';');

        // Les données
        Promoteur::with('microProjets')
            ->chunk(500, function ($promoteurs) use (
                $file,
                $promoteurColumns,
                $microProjetColumns
            ) {

                foreach ($promoteurs as $promoteur) {

                    // Un promoteur = un seul micro-projet
                    $microProjet = $promoteur->microProjets->first();

                    $row = [];

                    // Données du promoteur
                    foreach ($promoteurColumns as $column) {
                        $row[] = $promoteur->{$column};
                    }

                    // Données du micro-projet
                    foreach ($microProjetColumns as $column) {
                        $row[] = $microProjet?->{$column};
                    }

                    fputcsv($file, $row, ';');
                }
            });

        fclose($file);

    }, 200, $headers);
}
}
