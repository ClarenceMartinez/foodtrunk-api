<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    /**
     * POST /api/me/fcm-token
     * La app Flutter llama esto al iniciar sesion y cada vez que
     * FirebaseMessaging.instance.onTokenRefresh dispara un token nuevo.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $request->user()->update(['fcm_token' => $validated['fcm_token']]);

        return response()->json(['message' => 'Token registrado correctamente']);
    }
}
