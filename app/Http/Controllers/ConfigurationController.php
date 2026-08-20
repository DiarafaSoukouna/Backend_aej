<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configuration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ConfigurationController extends Controller
{
    public function index()
    {
        $configuration = Configuration::first();
        if (!$configuration) {
            return new JsonResponse(["Message" => "Configuration non trouvée"], 404);
        }
        return new JsonResponse(["Message" => "Configuration récupérée avec succès", "data" => $configuration], 200);
    }

    public function store(Request $request): JsonResponse
    {
        if (Configuration::exists()) {
            return new JsonResponse([
                "Message" => "Une configuration existe déjà"
            ], 409);
        }

        $validation = Validator::make($request->all(), [
            'logo_systeme' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'logo_structure' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'sigle_systeme' => 'required|string|max:255',
            'intitule_systeme' => 'required|string|max:255',
            'sigle_structure' => 'required|string|max:255',
            'intitule_structure' => 'required|string|max:255',
            'adresse_sociale_structure' => 'nullable|string|max:255',
            'email_structure' => 'nullable|email|max:255',
            'whatsapp_structure' => 'nullable|string|max:255',
            'telephone_structure' => 'nullable|string|max:255',
            'sigle_monnaie_pays' => 'required|string|max:255',
            'sigle_devise_principale' => 'required|string|max:255',
            'taux_devise_principale' => 'required|numeric',
            'mise_en_maintenance' => 'required|boolean',
            'delai_inactivite_minutes' => 'required|integer',
            'nombre_session_possible' => 'required|integer',
            'nombre_tentatives_connexion' => 'required|integer',
            'delai_code_otp_minutes' => 'required|integer',
            'delai_changement_mdp_mois' => 'required|integer',
            'delai_suppression_secondes' => 'required|integer',
            'code_instance_whatsapp' => 'nullable|string|max:255',
            'token_instance_whatsapp' => 'nullable|string|max:255',
            'email_notifications' => 'required|email|max:255',
            'mot_de_passe_email_notifications' => 'required|string|max:255',
            'smtp_email_notifications' => 'required|string|max:255',
            'smtp_host_notifications' => 'required|string|max:255',
            'smtp_port_notifications' => 'nullable|integer',
            'smtp_encrypt_notifications' => 'nullable|string|max:10'

        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try {
            $data = $validation->validated();
            if ($request->hasFile('logo_systeme')) {
                $data['logo_systeme'] = $request->file('logo_systeme')->store('configurations', 'public');
            }
            if ($request->hasFile('logo_structure')) {
                $data['logo_structure'] = $request->file('logo_structure')->store('configurations', 'public');
            }
            $configuration = Configuration::create($data);
            return new JsonResponse(["Message" => "Configuration créée avec succès", "data" => $configuration], 201);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la création de la configuration", "error" => $e->getMessage()], 500);
        }
    }

    public function patch(Request $request): JsonResponse
    {
        $configuration = Configuration::first();

        if (!$configuration) {
            return new JsonResponse([
                "Message" => "Configuration non trouvée"
            ], 404);
        }

        $validation = Validator::make($request->all(), [
            'logo_systeme' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'sigle_systeme' => 'sometimes|string|max:255',
            'intitule_systeme' => 'sometimes|string|max:255',

            'sigle_structure' => 'sometimes|string|max:255',
            'intitule_structure' => 'sometimes|string|max:255',
            'logo_structure' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'adresse_sociale_structure' => 'sometimes|nullable|string',
            'email_structure' => 'sometimes|email|max:255',
            'whatsapp_structure' => 'sometimes|string|max:255',
            'telephone_structure' => 'sometimes|string|max:255',

            'sigle_monnaie_pays' => 'sometimes|string|max:255',
            'sigle_devise_principale' => 'sometimes|string|max:255',
            'taux_devise_principale' => 'sometimes|numeric',

            'mise_en_maintenance' => 'sometimes|boolean',

            'delai_inactivite_minutes' => 'sometimes|integer',
            'nombre_session_possible' => 'sometimes|integer',
            'nombre_tentatives_connexion' => 'sometimes|integer',
            'delai_code_otp_minutes' => 'sometimes|integer',
            'delai_changement_mdp_mois' => 'sometimes|integer',
            'delai_suppression_secondes' => 'sometimes|integer',

            'code_instance_whatsapp' => 'sometimes|nullable|string|max:255',
            'token_instance_whatsapp' => 'sometimes|nullable|string|max:255',

            'email_notifications' => 'sometimes|email|max:255',
            'mot_de_passe_email_notifications' => 'sometimes|string|max:255',
            'smtp_email_notifications' => 'sometimes|string|max:255',
            'smtp_host_notifications' => 'sometimes|string|max:255',
            'smtp_port_notifications' => 'sometimes|integer',
            'smtp_encrypt_notifications' => 'sometimes|string|max:10',

            'lien_api_parent' => 'sometimes|nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                "Message" => "Validation échouée",
                "errors" => $validation->errors()
            ], 422);
        }

        try {
            $data = $validation->validated();
            if ($request->hasFile('logo_systeme')) {
                if ($configuration->logo_systeme && Storage::disk('public')->exists($configuration->logo_systeme)) {
                    Storage::disk('public')->delete($configuration->logo_systeme);
                }
                $data['logo_systeme'] = $request->file('logo_systeme')->store('configurations', 'public');
            }
            if ($request->hasFile('logo_structure')) {
                if ($configuration->logo_structure && Storage::disk('public')->exists($configuration->logo_structure)) {
                    Storage::disk('public')->delete($configuration->logo_structure);
                }
                $data['logo_structure'] = $request->file('logo_structure')->store('configurations', 'public');
            }
            $configuration->update($data);

            return new JsonResponse([
                "Message" => "Configuration mise à jour avec succès",
                "data" => $configuration->fresh()
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                "Message" => "Erreur lors de la mise à jour de la configuration",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    // public function destroy($id) : JsonResponse
    //     {
    //         $configuration = Configuration::find($id);
    //         if (!$configuration) {
    //             return new JsonResponse(["Message" => "Configuration non trouvée"], 404);
    //         }
    //         try{
    //             $configuration->delete();
    //             return new JsonResponse(["Message" => "Configuration supprimée avec succès"], 200);
    //         } catch (\Exception $e) {
    //             return new JsonResponse(["Message" => "Erreur lors de la suppression de la configuration", "error" => $e->getMessage()], 500);
    //         }
    //     }
}
