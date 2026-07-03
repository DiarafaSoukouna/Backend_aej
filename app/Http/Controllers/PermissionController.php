<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Permission;


class PermissionController extends Controller
{
    public function index() : JsonResponse
    {
      $permissions = Permission::all();
      return new JsonResponse(['Message' => 'Permissions retrieved successfully', 'data' => $permissions], 200);
    }
    public function store(Request $request) : JsonResponse
    {
      $validation = Validator::make($request->all(), [
      'role_id' => 'required|exists:roles,id',
      'module' => 'required|string|max:255',
      'autorise' => 'required|boolean',
      'acces' => 'required|boolean',
      'full_access' => 'required|boolean'
      ]);

      if ($validation->fails()) {
        return new JsonResponse([
          'message' => 'Validation failed',
          'errors' => $validation->errors()
        ], 422);
      }

      $permission = Permission::create($request->all());

      return new JsonResponse(['Message' => 'Permission created successfully', 'data' => $permission], 201);
    }
    public function show($id) : JsonResponse 
    {
        $permission = Permission::find($id);
    
        if (!$permission) {
            return new JsonResponse(['Message' => 'Permission not found'], 404);
        }
    
        return new JsonResponse(['Message' => 'Permission retrieved successfully', 'data' => $permission], 200);

    }
    public function update(Request $request, $id) : JsonResponse
    {
        $permission = Permission::find($id);
    
        if (!$permission) {
            return new JsonResponse(['Message' => 'Permission not found'], 404);
        }
    
        $validation = Validator::make($request->all(), [
          'role_id' => 'sometimes|required|exists:roles,id',
          'module' => 'sometimes|required|string|max:255',
          'autorise' => 'sometimes|required|boolean',
          'acces' => 'sometimes|required|boolean',
          'full_access' => 'sometimes|required|boolean'
        ]);
    
        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }
    
        $permission->update($request->all());
    
        return new JsonResponse(['Message' => 'Permission updated successfully', 'data' => $permission], 200);
    }
    public function destroy($id) : JsonResponse
    {
        $permission = Permission::find($id);
    
        if (!$permission) {
            return new JsonResponse(['Message' => 'Permission not found'], 404);
        }
    
        $permission->delete();
    
        return new JsonResponse(['Message' => 'Permission deleted successfully'], 200);
    }
}
