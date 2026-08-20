<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Document;

class DocumentController extends Controller
{
    /**
     * Servir un fichier depuis le stockage public
     */
    public function serve(Request $request, $path)
    {
        $safePath = str_replace('..', '', $path);
        $fullPath = storage_path('app/public/' . $safePath);
        
        if (!file_exists($fullPath))  return response()->noContent(404);
        
        $realPath = realpath($fullPath);
        $storagePath = realpath(storage_path('app/public'));
        
        if ($realPath === false || strpos($realPath, $storagePath) !== 0) {
            return response()->noContent(403);
        }
        
        $mimeType = mime_content_type($fullPath);
        
        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    /**
     * Upload de fichier générique
     */
    public function upload(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'file' => 'required|file|max:51200', // Max 50MB
            'folder' => 'required|string|max:255',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation échouée',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $folder = $request->input('folder');
            $file = $request->file('file');
            $safeFolder = str_replace('..', '', $folder);
            $safeFolder = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $safeFolder);
            $path = $file->store($safeFolder, 'public');
            
            return new JsonResponse([
                'message' => 'Fichier uploadé avec succès',
                'data' => [
                    'path' => $path,
                    'url' => url('/api/files/' . $path),
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de l\'upload',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un fichier
     */
    public function delete(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation échouée',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $path = $request->input('path');
            $safePath = str_replace('..', '', $path);
            
            if (Storage::disk('public')->exists($safePath)) {
                Storage::disk('public')->delete($safePath);
                
                return new JsonResponse([
                    'message' => 'Fichier supprimé avec succès'
                ], 200);
            }
            
            return new JsonResponse([
                'message' => 'Fichier non trouvé'
            ], 404);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload de document avec enregistrement en base de données
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'file' => 'required|file|max:51200', // Max 50MB
            'folder' => 'required|string|max:255',
            'micro_projet_id' => 'nullable|exists:micro_projets,id',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation échouée',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $folder = $request->input('folder');
            $file = $request->file('file');
            $safeFolder = str_replace('..', '', $folder);
            $safeFolder = preg_replace('/[^a-zA-Z0-9_\-\/]/', '', $safeFolder);
            $path = $file->store($safeFolder, 'public');
            
            $document = Document::create([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'url' => url('/api/files/' . $path),
                'created_by' => $request->user()->id,
                'micro_projet_id' => $request->input('micro_projet_id'),
            ]);
            
            return new JsonResponse([
                'message' => 'Document uploadé avec succès',
                'data' => $document->load(['createdBy', 'microProjet'])
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de l\'upload du document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les documents
     */
    public function indexDocuments(Request $request): JsonResponse
    {
        $query = Document::with(['createdBy', 'microProjet']);
        
        if ($request->has('micro_projet_id')) {
            $query->where('micro_projet_id', $request->input('micro_projet_id'));
        }
        
        if ($request->has('created_by')) {
            $query->where('created_by', $request->input('created_by'));
        }
        
        $documents = $query->get();
        
        return new JsonResponse([
            'message' => 'Documents récupérés avec succès',
            'data' => $documents
        ], 200);
    }

    /**
     * Afficher un document
     */
    public function showDocument($id): JsonResponse
    {
        $document = Document::with(['createdBy', 'microProjet'])->findOrFail($id);
        
        return new JsonResponse([
            'message' => 'Document récupéré avec succès',
            'data' => $document
        ], 200);
    }

    /**
     * Supprimer un document
     */
    public function deleteDocument($id): JsonResponse
    {
        $document = Document::findOrFail($id);
        
        try {
            // Supprimer le fichier physique
            if ($document->path && Storage::disk('public')->exists($document->path)) {
                Storage::disk('public')->delete($document->path);
            }
            
            // Supprimer l'enregistrement en base de données
            $document->delete();
            
            return new JsonResponse([
                'message' => 'Document supprimé avec succès'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la suppression du document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
