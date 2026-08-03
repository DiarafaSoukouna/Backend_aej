<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with(['microProjet'])->get();
        return new JsonResponse([
            'message' => 'Documents retrieved successfully',
            'data' => $documents
        ], 200);
    }

    public function show($id)
    {
        $document = Document::with(['microProjet'])->findOrFail($id);
        return new JsonResponse([
            'message' => 'Document retrieved successfully',
            'data' => $document
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'type_document' => 'required|string|max:100',
            'fichier' => 'required|string|max:255',
        ]);

        $document = Document::create($validated);
        return new JsonResponse([
            'message' => 'Document created successfully',
            'data' => $document
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);
        
        $validated = $request->validate([
            'micro_projet_id' => 'required|exists:micro_projets,id',
            'type_document' => 'required|string|max:100',
            'fichier' => 'required|string|max:255',
        ]);

        $document->update($validated);
        return new JsonResponse([
            'message' => 'Document updated successfully',
            'data' => $document
        ], 200);
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        $document->delete();
        return new JsonResponse([
            'message' => 'Document deleted successfully'
        ], 200);
    }
}
