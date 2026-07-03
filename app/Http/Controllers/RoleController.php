<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Role;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::all();
        return new JsonResponse(['Message' => 'Role list retrieved successfully', 'data' => $roles], 200);

    }
    public function show($id) : JsonResponse
    {
        $role = Role::find($id);
        if (!$role) {
            return new JsonResponse(['Message' => 'Role not found'], 404);
        }
        return new JsonResponse(['Message' => 'Role retrieved successfully', 'data' => $role], 200);
    }
    public function store(Request $request) : JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:roles',
            'libelle' => 'required|string|max:100|unique:roles',
            'description' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $role = Role::create($request->all());

            return new JsonResponse([
                'message' => 'Role created successfully',
                'data' => $role
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id) : JsonResponse
    {
        $role = Role::find($id);
        if (!$role) {
            return new JsonResponse(['Message' => 'Role not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:50|unique:roles,code,' . $id,
            'libelle' => 'sometimes|required|string|max:100|unique:roles,libelle,' . $id,
            'description' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $role->update($request->all());

            return new JsonResponse([
                'message' => 'Role updated successfully',
                'data' => $role
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id) : JsonResponse
    {
        $role = Role::find($id);
        if (!$role) {
            return new JsonResponse(['Message' => 'Role not found'], 404);
        }

        try {
            $role->delete();
            return new JsonResponse(['Message' => 'Role deleted successfully'], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting role',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
