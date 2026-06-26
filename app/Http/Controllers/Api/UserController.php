<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * POST /api/platform/users
     * El Platform Owner crea un usuario directamente (cualquier rol).
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'company_id' => $data['role'] === 'platform-owner' ? null : $data['company_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $user->assignRole($data['role']);

        return response()->json([
            'data' => new UserResource($user->load('company')),
            'message' => __('messages.user.created'),
        ], 201);
    }

    /**
     * GET /api/platform/users
     * Lista TODOS los usuarios de la plataforma, sin importar la empresa.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->with('company')->withTrashed(false);

        if ($request->filled('role')) {
            $query->role($request->string('role'));
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * GET /api/platform/users/{user}
     */
    public function show(User $user): JsonResponse
    {
        return response()->json(['data' => new UserResource($user->load('company'))]);
    }

    /**
     * PUT/PATCH /api/platform/users/{user}
     * Permite cambiar status y/o rol de cualquier usuario.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['role'])) {
            if ($user->id === $request->user()->id && $data['role'] !== 'platform-owner') {
                abort(422, __('messages.user.cannot_remove_own_role'));
            }
            $user->syncRoles([$data['role']]);
            unset($data['role']);
        }

        $user->update($data);

        return response()->json([
            'data' => new UserResource($user->load('company')),
            'message' => __('messages.user.updated'),
        ]);
    }

    /**
     * DELETE /api/platform/users/{user}
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_if($user->id === $request->user()->id, 422, __('messages.user.cannot_delete_self'));

        $user->delete();

        return response()->json(['message' => __('messages.user.deleted')]);
    }
}
