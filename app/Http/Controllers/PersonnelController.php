<?php

namespace App\Http\Controllers;

use App\Models\AgenceRegionale;
use App\Models\Fonction;
use App\Models\OrganismeFinancement;
use Illuminate\Http\Request;
use App\Models\Personnel;
use App\Models\Token;
use App\Models\Role;
use App\Models\Structure;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Services\MailService;


class PersonnelController extends Controller
{
    public function index(): JsonResponse
    {
        $query = Personnel::with('role', 'fonction', 'structure', 'agence', 'organisme');
        $filters = request()->only(['role_id', 'fonction_id', 'structure_id', 'agence_id', 'organisme_id', 'is_active']);

        foreach ($filters as $key => $value) {
            if (!empty($value)) $query->where($key, $value);
        }

        $perPage = request()->get('per_page', 20);
        $personnels = $query->paginate($perPage);

        return new JsonResponse([
            'message' => 'Personnels retrieved successfully',
            'data' => $personnels->items(),
            'pagination' => [
                'current_page' => $personnels->currentPage(),
                'per_page' => $personnels->perPage(),
                'total' => $personnels->total(),
                'last_page' => $personnels->lastPage(),
                'from' => $personnels->firstItem(),
                'to' => $personnels->lastItem(),
            ],
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $personnel = Personnel::with('role', 'agence', 'fonction', 'structure', 'organisme')->find($id);
        if (!$personnel) {
            return new JsonResponse(['Message' => 'Personnel not found'], 404);
        }
        return new JsonResponse(['Message' => 'Personnel retrieved successfully', 'data' => $personnel], 200);
    }

    public function store(Request $request, MailService $mailService): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:personnels',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'mot_de_passe' => 'nullable|string|min:8',
            'is_active' => 'boolean',
            'role_id' => 'required|exists:roles,id',
            'fonction_id' => 'required|exists:fonctions,id',
            'structure_id' => 'nullable|exists:structures,id',
            'agence_id' => 'nullable|exists:agences_regionales,id',
            'organisme_id' => 'nullable|exists:organisme_financements,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $data = $request->except('mot_de_passe');

            $personnel = Personnel::create(array_merge($data, [
                'mot_de_passe' => Hash::make('TEMP_PASSWORD_' . now()->format('YmdHis'))
            ]));

            $mailService->sendWelcomeEmail($personnel);
            $plainToken = bin2hex(random_bytes(32));
            $hashedToken = Hash::make($plainToken);

            Token::create([
                'personnel_id' => $personnel->id,
                'token' => $hashedToken,
                'type' => 'SETUP',
                'expires_at' => now()->addHours(24 * 30), // Valide 30 jours
                'created_at' => now(),
            ]);

            $setupUrl = config('mail.url') . '/setup-password?mode=setup&token=' . $plainToken;
            $mailService->sendSetupEmail($personnel, $setupUrl);

            return new JsonResponse([
                'message' => 'Personnel created successfully. Un email de configuration a été envoyé.',
                'data' => $personnel
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeMultiple(Request $request, MailService $mailService): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'personnels' => 'required|array|min:1',
            'personnels.*.nom' => 'required|string|max:255',
            'personnels.*.prenom' => 'required|string|max:255',
            'personnels.*.email' => 'required|string|email|max:255',
            'personnels.*.telephone' => 'nullable|string|max:100',
            'personnels.*.adresse' => 'nullable|string|max:255',
            'personnels.*.role_code' => 'nullable|string',
            'personnels.*.fonction_code' => 'nullable|string',
            'personnels.*.structure_code' => 'nullable|string',
            'personnels.*.agence_code' => 'nullable|string',
            'personnels.*.organisme_sigle' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $roleCodes = array_filter(array_unique(array_column($request->personnels, 'role_code')));
            $fonctionCodes = array_filter(array_unique(array_column($request->personnels, 'fonction_code')));
            $structureCodes = array_filter(array_unique(array_column($request->personnels, 'structure_code')));
            $agenceCodes = array_filter(array_unique(array_column($request->personnels, 'agence_code')));
            $organismeSigles = array_filter(array_unique(array_column($request->personnels, 'organisme_sigle')));

            $roles = Role::whereIn('code', $roleCodes)->pluck('id', 'code')->toArray();
            $fonctions = Fonction::whereIn('code', $fonctionCodes)->pluck('id', 'code')->toArray();
            $structures = Structure::whereIn('code', $structureCodes)->pluck('id', 'code')->toArray();
            $agences = AgenceRegionale::whereIn('code', $agenceCodes)->pluck('id', 'code')->toArray();
            $organismes = OrganismeFinancement::whereIn('sigle', $organismeSigles)->pluck('id', 'sigle')->toArray();

            $existingEmails = Personnel::whereIn('email', array_column($request->personnels, 'email'))->pluck('id', 'email')->toArray();
            $personnels = [];
            $tokensToCreate = [];
            $emailsToSend = [];

            foreach ($request->personnels as $personneData) {
                $personneData['role_id'] = $roles[$personneData['role_code']] ?? null;
                $personneData['fonction_id'] = $fonctions[$personneData['fonction_code']] ?? null;
                $personneData['structure_id'] = $structures[$personneData['structure_code']] ?? null;
                $personneData['agence_id'] = $agences[$personneData['agence_code']] ?? null;
                $personneData['organisme_id'] = $organismes[$personneData['organisme_sigle']] ?? null;

                unset(
                    $personneData['role_code'],
                    $personneData['fonction_code'],
                    $personneData['structure_code'],
                    $personneData['agence_code'],
                    $personneData['organisme_sigle']
                );

                if (isset($existingEmails[$personneData['email']])) {
                    Personnel::where('id', $existingEmails[$personneData['email']])->update($personneData);
                    continue;
                }

                $personnel = Personnel::create(array_merge($personneData, [
                    'mot_de_passe' => Hash::make('TEMP_PASSWORD_' . now()->format('YmdHis')),
                    'is_active' => 1
                ]));

                $plainToken = bin2hex(random_bytes(32));
                $tokensToCreate[] = [
                    'personnel_id' => $personnel->id,
                    'token' => Hash::make($plainToken),
                    'type' => 'SETUP',
                    'expires_at' => now()->addHours(24 * 30),
                    'created_at' => now(),
                ];

                $emailsToSend[] = [
                    'personnel' => $personnel,
                    'setup_url' => config('mail.url') . '/setup-password?mode=setup&token=' . $plainToken
                ];

                $personnels[] = $personnel;
            }

            if (!empty($tokensToCreate)) Token::insert($tokensToCreate);

            // foreach ($emailsToSend as $emailData) {
            //     $mailService->sendWelcomeEmail($emailData['personnel']);
            //     $mailService->sendSetupEmail($emailData['personnel'], $emailData['setup_url']);
            // }

            return new JsonResponse([
                'message' => 'Personnels created successfully. Un email de configuration a été envoyé.',
                'data' => $personnels,
                'total' => count($personnels)
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $personnel = Personnel::find($id);
        if (!$personnel) {
            return new JsonResponse(['Message' => 'Personnel not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:personnels,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'role_id' => 'sometimes|required|exists:roles,id',
            'fonction_id' => 'sometimes|required|exists:fonctions,id',
            'structure_id' => 'nullable|exists:structures,id',
            'agence_id' => 'nullable|exists:agences_regionales,id',
            'organisme_id' => 'nullable|exists:organisme_financements,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $personnel->update($validation->validated());

            return new JsonResponse([
                'message' => 'Personnel updated successfully',
                'data' => $personnel
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $personnel = Personnel::find($id);
        if (!$personnel) {
            return new JsonResponse(['Message' => 'Personnel not found'], 404);
        }

        try {
            $personnel->delete();
            return new JsonResponse(['Message' => 'Personnel deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting personnel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
