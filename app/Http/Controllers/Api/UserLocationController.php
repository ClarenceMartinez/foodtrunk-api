<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserLocationController extends Controller
{
    /**
     * POST /api/me/location
     * Guarda/actualiza la ULTIMA ubicacion conocida del usuario
     * (updateOrCreate: una sola fila por usuario, no historial).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $request->user()->lastLocation()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'last_seen_at' => now(),
            ],
        );

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
        ]);
    }
}
