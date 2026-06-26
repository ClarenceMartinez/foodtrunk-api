<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    /**
     * GET /api/me/notification-settings
     */
    public function show(Request $request): JsonResponse
    {
        $settings = $request->user()->notificationSettings();

        return response()->json([
            'success' => true,
            'data' => [
                'push_notifications_enabled' => $settings->push_notifications_enabled,
                'smart_nearby_alerts_enabled' => $settings->smart_nearby_alerts_enabled,
                'location_alerts_enabled' => $settings->location_alerts_enabled,
                'promotion_alerts_enabled' => $settings->promotion_alerts_enabled,
                'max_alerts_per_day' => $settings->max_alerts_per_day,
            ],
        ]);
    }

    /**
     * PATCH /api/me/notification-settings
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'push_notifications_enabled' => 'sometimes|boolean',
            'smart_nearby_alerts_enabled' => 'sometimes|boolean',
            'location_alerts_enabled' => 'sometimes|boolean',
            'promotion_alerts_enabled' => 'sometimes|boolean',
            'max_alerts_per_day' => 'sometimes|integer|min:0|max:20',
        ]);

        $settings = $request->user()->notificationSettings();
        $settings->update($validated);

        return response()->json([
            'success' => true,
            'data' => [
                'push_notifications_enabled' => $settings->push_notifications_enabled,
                'smart_nearby_alerts_enabled' => $settings->smart_nearby_alerts_enabled,
                'location_alerts_enabled' => $settings->location_alerts_enabled,
                'promotion_alerts_enabled' => $settings->promotion_alerts_enabled,
                'max_alerts_per_day' => $settings->max_alerts_per_day,
            ],
        ]);
    }
}
