<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::with(['personnel']);

        if ($request->has('personnel_id') && !empty($request->personnel_id))
            $notifications = $notifications->where('personnel_id', $request->personnel_id);

        $notificationData = $notifications->paginate($request->get('per_page', 20));

        return new JsonResponse([
            'message' => 'Notifications list retrieved successfully',
            'data' => $notificationData,
            'pagination' => [
                'current_page' => $notificationData->currentPage(),
                'last_page' => $notificationData->lastPage(),
                'per_page' => $notificationData->perPage(),
                'total' => $notificationData->total(),
            ]
        ], 200);
    }

    public function show($id): JsonResponse
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return new JsonResponse(['Message' => 'Notification not found'], 404);
        }
        return new JsonResponse(['Message' => 'Notification retrieved successfully', 'data' => $notification], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'personnel_id' => 'required|exists:personnels,id',
            'titre' => 'required|string|max:200',
            'message' => 'required|string',
            'lue' => 'sometimes|boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $notification = Notification::create($validation->validated());

            return new JsonResponse([
                'message' => 'Notification created successfully',
                'data' => $notification
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error creating notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return new JsonResponse(['Message' => 'Notification not found'], 404);
        }

        $validation = Validator::make($request->all(), [
            'personnel_id' => 'sometimes|required|exists:personnels,id',
            'titre' => 'sometimes|required|string|max:200',
            'message' => 'sometimes|required|string',
            'lue' => 'sometimes|boolean',
        ]);

        if ($validation->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 422);
        }

        try {
            $notification->update($validation->validated());

            return new JsonResponse([
                'message' => 'Notification updated successfully',
                'data' => $notification
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error updating notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return new JsonResponse(['Message' => 'Notification not found'], 404);
        }

        try {
            $notification->delete();
            return new JsonResponse(['Message' => 'Notification deleted successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error deleting notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead($id): JsonResponse
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return new JsonResponse(['Message' => 'Notification not found'], 404);
        }

        try {
            $notification->update(['lue' => true]);
            return new JsonResponse(['Message' => 'Notification marked as read successfully', 'data' => $notification], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Error marking notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByPersonnel($personnelId): JsonResponse
    {
        $notifications = Notification::where('personnel_id', $personnelId)->get();
        return new JsonResponse(['Message' => 'Notifications retrieved successfully', 'data' => $notifications], 200);
    }
}
