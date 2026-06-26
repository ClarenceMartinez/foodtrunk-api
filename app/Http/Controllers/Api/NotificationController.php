<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/consumer/notifications
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $notifications->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->data['type'] ?? null,
                'title' => $n->data['title'] ?? null,
                'message' => $n->data['message'] ?? null,
                'food_truck_id' => $n->data['food_truck_id'] ?? null,
                'food_truck_name' => $n->data['food_truck_name'] ?? null,
                'food_truck_logo_url' => $n->data['food_truck_logo_url'] ?? null,
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ]),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'total' => $notifications->total(),
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * PATCH /api/consumer/notifications/{notification}/read
     */
    public function markAsRead(Request $request, string $notification): JsonResponse
    {
        $notif = $request->user()->notifications()->where('id', $notification)->first();

        abort_if($notif === null, 404);

        $notif->markAsRead();

        return response()->json(['message' => 'Notificacion marcada como leida']);
    }

    /**
     * POST /api/consumer/notifications/read-all
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Todas las notificaciones marcadas como leidas']);
    }
}
