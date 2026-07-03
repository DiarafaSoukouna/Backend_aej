<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configuration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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
    public function store(Request $request) : JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'sigle_systeme' => 'required|string|max:255',
            'intitule_systeme' => 'required|string|max:255',
            'sigle_structure' => 'required|string|max:255',
            'intitule_structure' => 'required|string|max:255',
            'logo_structure' => 'nullable|string|max:255',
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
            'delai_code_tp_minutes' => 'required|integer',
            'delai_changement_mdp_mois' => 'required|integer',
            'delai_suppression_secondes' => 'required|integer',
            'code_instance_whatsapp' => 'nullable|string|max:255',
            'email_notifications' => 'nullable|email|max:255',
            'mot_de_passe_email_notifications' => 'nullable|string|max:255',
            'smtp_email_notifications' => 'nullable|string|max:255',
            'lien_api_parent' => 'nullable|string|max:255'
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $configuration = Configuration::create($request->all());
            return new JsonResponse(["Message" => "Configuration créée avec succès", "data" => $configuration], 201);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la création de la configuration", "error" => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id) : JsonResponse
    {
        $configuration = Configuration::find($id);
        if (!$configuration) {
            return new JsonResponse(["Message" => "Configuration non trouvée"], 404);
        }
        $validation = Validator::make($request->all(), [
            'sigle_systeme' => 'required|string|max:255',
            'intitule_systeme' => 'required|string|max:255',
            'sigle_structure' => 'required|string|max:255',
            'intitule_structure' => 'required|string|max:255',
            'logo_structure' => 'nullable|string|max:255',
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
            'delai_code_tp_minutes' => 'required|integer',
            'delai_changement_mdp_mois' => 'required|integer',
            'delai_suppression_secondes' => 'required|integer',
            'code_instance_whatsapp' => 'nullable|string|max:255',
            'email_notifications' => 'nullable|email|max:255',
            'mot_de_passe_email_notifications' => 'nullable|string|max:255',
            'smtp_email_notifications' => 'nullable|string|max:255',
            'lien_api_parent' => 'nullable|string|max:255'
        ]);
        if ($validation->fails()) {
            return new JsonResponse(["Message" => "Validation échouée", "errors" => $validation->errors()], 422);
        }
        try{
            $configuration->update($request->all());
            return new JsonResponse(["Message" => "Configuration mise à jour avec succès", "data" => $configuration], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la mise à jour de la configuration", "error" => $e->getMessage()], 500);
        }
}
public function destroy($id) : JsonResponse
    {
        $configuration = Configuration::find($id);
        if (!$configuration) {
            return new JsonResponse(["Message" => "Configuration non trouvée"], 404);
        }
        try{
            $configuration->delete();
            return new JsonResponse(["Message" => "Configuration supprimée avec succès"], 200);
        } catch (\Exception $e) {
            return new JsonResponse(["Message" => "Erreur lors de la suppression de la configuration", "error" => $e->getMessage()], 500);
        }
    }
}