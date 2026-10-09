<?php

namespace App\Http\Controllers;

use App\Models\Promoteur;
use App\Models\Sexe;
use App\Models\Personnel;
use App\Models\LieuHabitation;
use App\Models\AgenceRegionale;
use App\Models\Secteur;
use App\Models\SousSecteur;
use App\Models\SituationMatrimoniale;
use App\Models\TypeSituationHandicap;
use App\Models\TypePieceIdentite;
use App\Models\NiveauEtude;
use App\Models\Pays;
use App\Models\Token;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PromoteurController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Promoteur::with([
            'sexe',
            'personnel',
            'lieuHabitation',
            'agenceRegionale',
            'secteurActivite',
            'sousSecteurActivite',
            'situationMatrimoniale',
            'typeSituationHandicap',
            'typePieceIdentite',
            'niveauEtude',
            'paysNationalite'
        ]);

        $filters = [
            'sexe_id', 'personnel_id', 'lieuhabitation_id', 'agenceregionale_id', 'secteuractivite_id',
            'soussecteuractivite_id', 'situationmatrimoniale_id', 'typesituationhandicap_id',
            'typepieceidentite_id', 'niveauetude_id', 'paysnationalite_id', 'statut'
        ];

        foreach ($filters as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('matriculeaej', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 20);
        $promoteurs = $query->paginate($perPage);

        return new JsonResponse([
            'message' => 'Promoteurs retrieved successfully',
            'data' => $promoteurs->items(),
            'pagination' => [
                'current_page' => $promoteurs->currentPage(),
                'per_page' => $promoteurs->perPage(),
                'total' => $promoteurs->total(),
                'last_page' => $promoteurs->lastPage(),
                'from' => $promoteurs->firstItem(),
                'to' => $promoteurs->lastItem(),
            ],
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $promoteur = Promoteur::with([
            'sexe',
            'personnel',
            'lieuHabitation',
            'agenceRegionale',
            'secteurActivite',
            'sousSecteurActivite',
            'situationMatrimoniale',
            'typeSituationHandicap',
            'typePieceIdentite',
            'niveauEtude',
            'paysNationalite',
            'microProjets',
            'remboursements',
        ])->find($id);

        if (!$promoteur) {
            return new JsonResponse(['message' => 'Promoteur not found'], 404);
        }

        return new JsonResponse([
            'message' => 'Promoteur retrieved successfully',
            'data' => $promoteur
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|string|email|max:180|unique:promoteurs',
            'telephone' => 'required|string|max:20|unique:promoteurs',
            'profile' => 'nullable|string',
            'tranche_age' => 'nullable|in:18_40,PLUS_40',
            'datenaissance' => 'nullable|date',
            'lieunaissance' => 'nullable|string|max:150',
            'matriculeaej' => 'nullable|string|max:50|unique:promoteurs',
            'numerocni' => 'nullable|string|max:50|unique:promoteurs',
            'numerocmu' => 'nullable|string|max:50|unique:promoteurs',
            'numerocnps' => 'nullable|string|max:50|unique:promoteurs',
            'raison_sociale' => 'nullable|string|max:200',
            'handicap' => 'nullable|string|max:100',
            'nomdupere' => 'nullable|string|max:200',
            'nomdelamere' => 'nullable|string|max:200',
            'sexe_id' => 'nullable|exists:sexes,id',
            'personnel_id' => 'nullable|exists:personnels,id',
            'lieuhabitation_id' => 'nullable|exists:lieux_habitation,id',
            'agenceregionale_id' => 'nullable|exists:agences_regionales,id',
            'secteuractivite_id' => 'nullable|exists:secteurs,id',
            'soussecteuractivite_id' => 'nullable|exists:sous_secteurs,id',
            'situationmatrimoniale_id' => 'nullable|exists:situation_matrimoniale,id',
            'typesituationhandicap_id' => 'nullable|exists:types_situation_handicap,id',
            'typepieceidentite_id' => 'nullable|exists:type_pieces_identite,id',
            'niveauetude_id' => 'nullable|exists:niveau_etude,id',
            'paysnationalite_id' => 'nullable|exists:pays,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $promoteur = Promoteur::create($validation->validated());

            return new JsonResponse([
                'message' => 'Promoteur created successfully',
                'data' => $promoteur
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating promoteur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeMultiple(Request $request, MailService $mailService): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'promoteurs' => 'required|array|min:1',
            'promoteurs.*.nom' => 'required|string|max:100',
            'promoteurs.*.prenom' => 'required|string|max:100',
            'promoteurs.*.email' => 'required|string|email|max:180',
            'promoteurs.*.telephone' => 'required|string|max:20',
            'promoteurs.*.profile' => 'nullable|string',
            'promoteurs.*.tranche_age' => 'nullable|in:18_40,PLUS_40',
            'promoteurs.*.datenaissance' => 'nullable|date',
            'promoteurs.*.lieunaissance' => 'nullable|string|max:150',
            'promoteurs.*.matriculeaej' => 'nullable|string|max:50',
            'promoteurs.*.numerocni' => 'nullable|string|max:50',
            'promoteurs.*.numerocmu' => 'nullable|string|max:50',
            'promoteurs.*.numerocnps' => 'nullable|string|max:50',
            'promoteurs.*.raison_sociale' => 'nullable|string|max:200',
            'promoteurs.*.handicap' => 'nullable|string|max:100',
            'promoteurs.*.nomdupere' => 'nullable|string|max:200',
            'promoteurs.*.nomdelamere' => 'nullable|string|max:200',
            'promoteurs.*.sexe_libelle' => 'nullable|string',
            'promoteurs.*.lieuhabitation_nom' => 'nullable|string',
            'promoteurs.*.agenceregionale_code' => 'nullable|string',
            'promoteurs.*.secteuractivite_libelle' => 'nullable|string',
            'promoteurs.*.soussecteuractivite_libelle' => 'nullable|string',
            'promoteurs.*.situationmatrimoniale_libelle' => 'nullable|string',
            'promoteurs.*.typesituationhandicap_libelle' => 'nullable|string',
            'promoteurs.*.typepieceidentite_libelle' => 'nullable|string',
            'promoteurs.*.niveauetude_libelle' => 'nullable|string',
            'promoteurs.*.paysnationalite_code_iso' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $sexeLibelles = array_filter(array_unique(array_column($request->promoteurs, 'sexe_libelle')));
            $lieuhabitationNoms = array_filter(array_unique(array_column($request->promoteurs, 'lieuhabitation_nom')));
            $agenceregionaleCodes = array_filter(array_unique(array_column($request->promoteurs, 'agenceregionale_code')));
            $secteuractiviteLibelles = array_filter(array_unique(array_column($request->promoteurs, 'secteuractivite_libelle')));
            $soussecteuractiviteLibelles = array_filter(array_unique(array_column($request->promoteurs, 'soussecteuractivite_libelle')));
            $situationmatrimonialeLibelles = array_filter(array_unique(array_column($request->promoteurs, 'situationmatrimoniale_libelle')));
            $typesituationhandicapLibelles = array_filter(array_unique(array_column($request->promoteurs, 'typesituationhandicap_libelle')));
            $typepieceidentiteLibelles = array_filter(array_unique(array_column($request->promoteurs, 'typepieceidentite_libelle')));
            $niveauetudeLibelles = array_filter(array_unique(array_column($request->promoteurs, 'niveauetude_libelle')));
            $paysnationaliteCodes = array_filter(array_unique(array_column($request->promoteurs, 'paysnationalite_code_iso')));

            $sexes = Sexe::whereIn('libelle', $sexeLibelles)->pluck('id', 'libelle')->toArray();
            $lieuhabitations = LieuHabitation::whereIn('nom', $lieuhabitationNoms)->pluck('id', 'nom')->toArray();
            $agenceregionales = AgenceRegionale::whereIn('code', $agenceregionaleCodes)->pluck('id', 'code')->toArray();
            $secteuractivites = Secteur::whereIn('libelle', $secteuractiviteLibelles)->pluck('id', 'libelle')->toArray();
            $soussecteuractivites = SousSecteur::whereIn('libelle', $soussecteuractiviteLibelles)->pluck('id', 'libelle')->toArray();
            $situationmatrimoniales = SituationMatrimoniale::whereIn('libelle', $situationmatrimonialeLibelles)->pluck('id', 'libelle')->toArray();
            $typesituationhandicaps = TypeSituationHandicap::whereIn('libelle', $typesituationhandicapLibelles)->pluck('id', 'libelle')->toArray();
            $typepieceidentites = TypePieceIdentite::whereIn('libelle', $typepieceidentiteLibelles)->pluck('id', 'libelle')->toArray();
            $niveauetudes = NiveauEtude::whereIn('libelle', $niveauetudeLibelles)->pluck('id', 'libelle')->toArray();
            $paysnationalites = Pays::whereIn('code_iso', $paysnationaliteCodes)->pluck('id', 'code_iso')->toArray();

            $existingTelephones = Promoteur::whereIn('telephone', array_column($request->promoteurs, 'telephone'))->pluck('id', 'telephone')->toArray();
            $existingMatricules = Promoteur::whereIn('matriculeaej', array_column($request->promoteurs, 'matriculeaej'))->pluck('id', 'matriculeaej')->toArray();
            $existingEmails = Promoteur::whereIn('email', array_column($request->promoteurs, 'email'))->pluck('id', 'email')->toArray();
            $personnels = Personnel::whereIn('email', array_column($request->promoteurs, 'email'))->pluck('id', 'email')->toArray();

            $promoteurs = [];
            $tokensToCreate = [];
            $emailsToSend = [];

            foreach ($request->promoteurs as $promoteurData) {
                if (!isset($personnels[$promoteurData['email']])) {
                    $personnel = Personnel::create([
                        'nom' => $promoteurData['nom'],
                        'prenom' => $promoteurData['prenom'],
                        'email' => $promoteurData['email'],
                        'telephone' => $promoteurData['telephone'],
                        'mot_de_passe' => Hash::make('TEMP_PASSWORD_' . now()->format('YmdHis')),
                        'is_active' => true,
                    ]);

                    $personnels[$promoteurData['email']] = $personnel->id;
                    $plainToken = bin2hex(random_bytes(32));
                    $tokensToCreate[] = [
                        'personnel_id' => $personnel->id,
                        'token' => Hash::make($plainToken),
                        'type' => 'SETUP',
                        'expires_at' => now()->addHours(24 * 30),
                        'created_at' => now(),
                    ];

                    $setupUrl = config('mail.url') . '/setup-password?mode=setup&token=' . $plainToken;
                    $emailsToSend[] = ['personnel' => $personnel, 'setup_url' => $setupUrl];
                }

                $promoteurData['sexe_id'] = $sexes[$promoteurData['sexe_libelle']] ?? null;
                $promoteurData['personnel_id'] = $personnels[$promoteurData['email']] ?? null;
                $promoteurData['lieuhabitation_id'] = $lieuhabitations[$promoteurData['lieuhabitation_nom']] ?? null;
                $promoteurData['agenceregionale_id'] = $agenceregionales[$promoteurData['agenceregionale_code']] ?? null;
                $promoteurData['secteuractivite_id'] = $secteuractivites[$promoteurData['secteuractivite_libelle']] ?? null;
                $promoteurData['soussecteuractivite_id'] = $soussecteuractivites[$promoteurData['soussecteuractivite_libelle']] ?? null;
                $promoteurData['situationmatrimoniale_id'] = $situationmatrimoniales[$promoteurData['situationmatrimoniale_libelle']] ?? null;
                $promoteurData['typesituationhandicap_id'] = $typesituationhandicaps[$promoteurData['typesituationhandicap_libelle']] ?? null;
                $promoteurData['typepieceidentite_id'] = $typepieceidentites[$promoteurData['typepieceidentite_libelle']] ?? null;
                $promoteurData['niveauetude_id'] = $niveauetudes[$promoteurData['niveauetude_libelle']] ?? null;
                $promoteurData['paysnationalite_id'] = $paysnationalites[$promoteurData['paysnationalite_code_iso']] ?? null;

                unset(
                    $promoteurData['sexe_libelle'],
                    $promoteurData['lieuhabitation_nom'],
                    $promoteurData['agenceregionale_code'],
                    $promoteurData['secteuractivite_libelle'],
                    $promoteurData['soussecteuractivite_libelle'],
                    $promoteurData['situationmatrimoniale_libelle'],
                    $promoteurData['typesituationhandicap_libelle'],
                    $promoteurData['typepieceidentite_libelle'],
                    $promoteurData['niveauetude_libelle'],
                    $promoteurData['paysnationalite_code_iso']
                );

                if (isset($existingTelephones[$promoteurData['telephone']])) {
                    Promoteur::where('id', $existingTelephones[$promoteurData['telephone']])->update($promoteurData);
                    continue;
                }

                if (!empty($promoteurData['matriculeaej']) && isset($existingMatricules[$promoteurData['matriculeaej']])) {
                    Promoteur::where('id', $existingMatricules[$promoteurData['matriculeaej']])->update($promoteurData);
                    continue;
                }

                if (!empty($promoteurData['email']) && isset($existingEmails[$promoteurData['email']])) {
                    Promoteur::where('id', $existingEmails[$promoteurData['email']])->update($promoteurData);
                    continue;
                }

                $promoteur = Promoteur::create($promoteurData);
                $promoteurs[] = $promoteur;
            }

            if (!empty($tokensToCreate)) Token::insert($tokensToCreate);

            foreach ($emailsToSend as $emailData) {
                $mailService->sendWelcomeEmail($emailData['personnel']);
                $mailService->sendSetupEmail($emailData['personnel'], $emailData['setup_url']);
            }

            return new JsonResponse([
                'message' => 'Promoteurs created successfully',
                'data' => $promoteurs,
                'total' => count($promoteurs)
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating promoteurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $promoteur = Promoteur::find($id);
        if (!$promoteur) {
            return new JsonResponse(['message' => 'Promoteur not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:100',
            'prenom' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|nullable|string|email|max:180|unique:promoteurs,email,' . $id,
            'telephone' => 'sometimes|required|string|max:20|unique:promoteurs,telephone,' . $id,
            'profile' => 'nullable|string',
            'tranche_age' => 'nullable|in:18_40,PLUS_40',
            'datenaissance' => 'nullable|date',
            'lieunaissance' => 'nullable|string|max:150',
            'matriculeaej' => 'nullable|string|max:50|unique:promoteurs,matriculeaej,' . $id,
            'numerocni' => 'nullable|string|max:50|unique:promoteurs,numerocni,' . $id,
            'numerocmu' => 'nullable|string|max:50|unique:promoteurs,numerocmu,' . $id,
            'numerocnps' => 'nullable|string|max:50|unique:promoteurs,numerocnps,' . $id,
            'raison_sociale' => 'nullable|string|max:200',
            'handicap' => 'nullable|string|max:100',
            'nomdupere' => 'nullable|string|max:200',
            'nomdelamere' => 'nullable|string|max:200',
            'sexe_id' => 'nullable|exists:sexes,id',
            'personnel_id' => 'nullable|exists:personnels,id',
            'lieuhabitation_id' => 'nullable|exists:lieux_habitation,id',
            'agenceregionale_id' => 'nullable|exists:agences_regionales,id',
            'secteuractivite_id' => 'nullable|exists:secteurs,id',
            'soussecteuractivite_id' => 'nullable|exists:sous_secteurs,id',
            'situationmatrimoniale_id' => 'nullable|exists:situation_matrimoniale,id',
            'typesituationhandicap_id' => 'nullable|exists:types_situation_handicap,id',
            'typepieceidentite_id' => 'nullable|exists:type_pieces_identite,id',
            'niveauetude_id' => 'nullable|exists:niveau_etude,id',
            'paysnationalite_id' => 'nullable|exists:pays,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $promoteur->update($validation->validated());

            return new JsonResponse([
                'message' => 'Promoteur updated successfully',
                'data' => $promoteur
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating promoteur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function patch(Request $request, $id): JsonResponse
    {
        $promoteur = Promoteur::find($id);
        if (!$promoteur) {
            return new JsonResponse(['message' => 'Promoteur not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:100',
            'prenom' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|nullable|string|email|max:180|unique:promoteurs,email,' . $id,
            'telephone' => 'sometimes|required|string|max:20|unique:promoteurs,telephone,' . $id,
            'profile' => 'nullable|string',
            'tranche_age' => 'nullable|in:18_40,PLUS_40',
            'datenaissance' => 'nullable|date',
            'lieunaissance' => 'nullable|string|max:150',
            'matriculeaej' => 'nullable|string|max:50|unique:promoteurs,matriculeaej,' . $id,
            'numerocni' => 'nullable|string|max:50|unique:promoteurs,numerocni,' . $id,
            'numerocmu' => 'nullable|string|max:50|unique:promoteurs,numerocmu,' . $id,
            'numerocnps' => 'nullable|string|max:50|unique:promoteurs,numerocnps,' . $id,
            'raison_sociale' => 'nullable|string|max:200',
            'handicap' => 'nullable|string|max:100',
            'nomdupere' => 'nullable|string|max:200',
            'nomdelamere' => 'nullable|string|max:200',
            'sexe_id' => 'nullable|exists:sexes,id',
            'personnel_id' => 'nullable|exists:personnels,id',
            'lieuhabitation_id' => 'nullable|exists:lieux_habitation,id',
            'agenceregionale_id' => 'nullable|exists:agences_regionales,id',
            'secteuractivite_id' => 'nullable|exists:secteurs,id',
            'soussecteuractivite_id' => 'nullable|exists:sous_secteurs,id',
            'situationmatrimoniale_id' => 'nullable|exists:situation_matrimoniale,id',
            'typesituationhandicap_id' => 'nullable|exists:types_situation_handicap,id',
            'typepieceidentite_id' => 'nullable|exists:type_pieces_identite,id',
            'niveauetude_id' => 'nullable|exists:niveau_etude,id',
            'paysnationalite_id' => 'nullable|exists:pays,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $promoteur->update(array_filter($validation->validated()));
            return new JsonResponse([
                'message' => 'Promoteur patched successfully',
                'data' => $promoteur
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error patching promoteur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $promoteur = Promoteur::find($id);
        if (!$promoteur) {
            return new JsonResponse(['message' => 'Promoteur not found'], 404);
        }

        try {
            $promoteur->delete();
            return new JsonResponse(['message' => 'Promoteur deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting promoteur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
