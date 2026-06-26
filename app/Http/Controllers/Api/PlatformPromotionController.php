<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromotionResource;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformPromotionController extends Controller
{
    /**
     * GET /api/platform/promotions
     * Vista de SOLO LECTURA: todas las promociones de todas las empresas.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Promotion::query()->with(['company', 'foodTruck']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }

        $promotions = $query->latest()->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => PromotionResource::collection($promotions->items()),
            'meta' => [
                'current_page' => $promotions->currentPage(),
                'last_page' => $promotions->lastPage(),
                'total' => $promotions->total(),
            ],
        ]);
    }
}
