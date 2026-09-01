<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\LotImportation;
use App\Models\MicroProjet;

class LotImportationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LotImportation::with(['microProjet']);
        
        $filters = ['micro_projet_id', 'code', 'nom_promoteur', 'prenom_promoteur'];
        foreach ($filters as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }

        $perPage = $request->get('per_page', 20);
        $lotImportations = $query->paginate($perPage);

        return new JsonResponse([
            'message' => 'Lots importation retrieved successfully',
            'data' => $lotImportations->items(),
            'pagination' => [
                'current_page' => $lotImportations->currentPage(),
                'per_page' => $lotImportations->perPage(),
                'total' => $lotImportations->total(),
                'last_page' => $lotImportations->lastPage(),
                'from' => $lotImportations->firstItem(),
                'to' => $lotImportations->lastItem(),
            ],
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $lotImportation = LotImportation::with(['microProjet'])->find($id);
        if (!$lotImportation) {
            return new JsonResponse(['message' => 'Lot importation not found'], 404);
        }

        return new JsonResponse([
            'message' => 'Lot importation retrieved successfully',
            'data' => $lotImportation
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|max:50',
            'nom_promoteur' => 'required|string|max:100',
            'prenom_promoteur' => 'required|string|max:100',
            'montant_sollicite' => 'required|numeric|min:0',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $code = $request->input('code');
            $microProjet = MicroProjet::where('code', $code)->first();
            
            if (!$microProjet) {
                return new JsonResponse([
                    'message' => 'Aucun micro-projet trouvé avec le code ' . $code . '. Importation ignorée.',
                    'code' => $code,
                    'imported' => false
                ], 200);
            }

            $lotImportation = LotImportation::create([
                'micro_projet_id' => $microProjet->id,
                'code' => $code,
                'nom_promoteur' => $request->input('nom_promoteur'),
                'prenom_promoteur' => $request->input('prenom_promoteur'),
                'montant_sollicite' => $request->input('montant_sollicite'),
            ]);

            return new JsonResponse([
                'message' => 'Lot importation created successfully',
                'data' => $lotImportation->load('microProjet')
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error creating lot importation', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $lotImportation = LotImportation::find($id);
        if (!$lotImportation) {
            return new JsonResponse(['message' => 'Lot importation not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:50',
            'nom_promoteur' => 'sometimes|required|string|max:100',
            'prenom_promoteur' => 'sometimes|required|string|max:100',
            'montant_sollicite' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validation->fails()) {
            return new JsonResponse(['message' => 'Validation failed', 'errors' => $validation->errors()], 422);
        }

        try {
            $data = $validation->validated();
            if ($request->has('code')) {
                $microProjet = MicroProjet::where('code', $request->input('code'))->first();
                if ($microProjet) {
                    $data['micro_projet_id'] = $microProjet->id;
                } else {
                    return new JsonResponse([
                        'message' => 'Aucun micro-projet trouvé avec le code ' . $request->input('code'),
                        'imported' => false
                    ], 400);
                }
            }

            $lotImportation->update($data);
            return new JsonResponse([
                'message' => 'Lot importation updated successfully',
                'data' => $lotImportation->load('microProjet')
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error updating lot importation', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $lotImportation = LotImportation::find($id);
        if (!$lotImportation) {
            return new JsonResponse(['message' => 'Lot importation not found'], 404);
        }

        try {
            $lotImportation->delete();
            return new JsonResponse(['message' => 'Lot importation deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error deleting lot importation', 'error' => $e->getMessage()], 500);
        }
    }
}
