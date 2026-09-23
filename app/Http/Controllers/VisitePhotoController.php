<?php

namespace App\Http\Controllers;

use App\Models\VisitePhoto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class VisitePhotoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = VisitePhoto::with(['exploitation', 'prisePar']);

        $filters = ['exploitation_id', 'prise_par_id'];
        foreach ($filters as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }

        $perPage = $request->get('per_page', 15);
        $photos = $query->paginate($perPage);
        
        return response()->json([
            'message' => 'Photos retrieved successfully', 
            'data' => $photos->items(),
            'pagination' => [
                'current_page' => $photos->currentPage(),
                'per_page' => $photos->perPage(),
                'total' => $photos->total(),
                'last_page' => $photos->lastPage(),
                'from' => $photos->firstItem(),
                'to' => $photos->lastItem(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $photo = VisitePhoto::with(['exploitation', 'prisePar'])->findOrFail($id);
        return response()->json(['message' => 'Photo retrieved successfully', 'data' => $photo]);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'exploitation_id' => 'required|exists:exploitations,id',
            'photo_url' => 'required|string|max:500',
            'description' => 'nullable|string|max:255',
            'prise_le' => 'nullable|date',
            'prise_par_id' => 'nullable|exists:personnels,id',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $photo = VisitePhoto::create($validation->validated());
            return response()->json(['message' => 'Photo created successfully', 'data' => $photo], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Photo creation failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $photo = VisitePhoto::findOrFail($id);

        $validation = Validator::make($request->all(), [
            'exploitation_id' => 'nullable|exists:exploitations,id',
            'photo_url' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:255',
            'prise_le' => 'nullable|date',
            'prise_par_id' => 'nullable|exists:personnels,id',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $photo->update($validation->validated());
            return response()->json(['message' => 'Photo updated successfully', 'data' => $photo]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Photo update failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $photo = VisitePhoto::findOrFail($id);
        try {
            $photo->delete();
            return response()->json(['message' => 'Photo deleted successfully'], 204);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Photo deletion failed',
                'errors' => $th->getMessage()
            ], 500);
        }
    }
}
