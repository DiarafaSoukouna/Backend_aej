<?php

namespace App\Http\Controllers;

use App\Models\VisitePhoto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VisitePhotoController extends Controller
{
    public function index(): JsonResponse
    {
        $photos = VisitePhoto::with(['exploitation', 'prisePar'])->get();
        return response()->json(['message' => 'Photos retrieved successfully', 'data' => $photos]);
    }

    public function show($id): JsonResponse
    {
        $photo = VisitePhoto::with(['exploitation', 'prisePar'])->findOrFail($id);
        return response()->json(['message' => 'Photo retrieved successfully', 'data' => $photo]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exploitation_id' => 'required|exists:exploitations,id',
            'photo_url' => 'required|string|max:500',
            'description' => 'nullable|string|max:255',
            'prise_le' => 'nullable|date',
            'prise_par_id' => 'nullable|exists:personnels,id',
        ]);

        $photo = VisitePhoto::create($validated);
        return response()->json(['message' => 'Photo created successfully', 'data' => $photo], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $photo = VisitePhoto::findOrFail($id);

        $validated = $request->validate([
            'exploitation_id' => 'nullable|exists:exploitations,id',
            'photo_url' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:255',
            'prise_le' => 'nullable|date',
            'prise_par_id' => 'nullable|exists:personnels,id',
        ]);

        $photo->update($validated);
        return response()->json(['message' => 'Photo updated successfully', 'data' => $photo]);
    }

    public function destroy($id): JsonResponse
    {
        $photo = VisitePhoto::findOrFail($id);
        $photo->delete();
        return response()->json(['message' => 'Photo deleted successfully'], 204);
    }
}
