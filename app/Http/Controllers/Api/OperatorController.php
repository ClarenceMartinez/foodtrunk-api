<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOperatorRequest;
use App\Http\Requests\UpdateOperatorRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OperatorController extends Controller
{
    /**
     * GET /api/company/operators
     * Solo operadores de la empresa del usuario autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $operators = User::role('operator')
            ->where('company_id', $request->user()->company_id)
            ->latest()
            ->get();

        return response()->json(['data' => UserResource::collection($operators)]);
    }

    /**
     * POST /api/company/operators
     */
    public function store(StoreOperatorRequest $request): JsonResponse
    {
        $data = $request->validated();

        $operator = User::create([
            'company_id' => $request->user()->company_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $operator->assignRole('operator');

        return response()->json([
            'data' => new UserResource($operator),
            'message' => __('messages.operator.created'),
        ], 201);
    }

    /**
     * GET /api/company/operators/{operator}
     */
    public function show(Request $request, User $operator): JsonResponse
    {
        $this->ensureOperatorBelongsToCompany($request, $operator);

        return response()->json(['data' => new UserResource($operator)]);
    }

    /**
     * PUT/PATCH /api/company/operators/{operator}
     */
    public function update(UpdateOperatorRequest $request, User $operator): JsonResponse
    {
        $this->ensureOperatorBelongsToCompany($request, $operator);

        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $operator->update($data);

        return response()->json([
            'data' => new UserResource($operator),
            'message' => __('messages.operator.updated'),
        ]);
    }

    /**
     * DELETE /api/company/operators/{operator}
     */
    public function destroy(Request $request, User $operator): JsonResponse
    {
        $this->ensureOperatorBelongsToCompany($request, $operator);

        $operator->delete();

        return response()->json(['message' => __('messages.operator.deleted')]);
    }

    /**
     * Evita que una empresa edite/elimine operadores de otra empresa,
     * y evita que se use este endpoint sobre un usuario que no es operador.
     */
    private function ensureOperatorBelongsToCompany(Request $request, User $operator): void
    {
        abort_if(
            $operator->company_id !== $request->user()->company_id || ! $operator->hasRole('operator'),
            404,
            __('messages.operator.not_found')
        );
    }
}
