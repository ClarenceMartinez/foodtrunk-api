<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterConsumerRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('messages.auth.invalid_credentials')],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => [__('messages.auth.account_inactive')],
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => $user->load('company', 'roles'),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * POST /api/auth/register-company
     * Registro público: crea la Empresa + su primer usuario (company-admin).
     */
    public function registerCompany(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_email' => ['required', 'email', 'unique:companies,email'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $company = Company::create([
            'name' => $data['company_name'],
            'slug' => Str::slug($data['company_name']).'-'.Str::random(5),
            'email' => $data['company_email'],
            'status' => 'pending', // requiere aprobación del Platform Owner
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $user->assignRole('company-admin');

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'data' => [
                'company' => $company,
                'user' => $user->load('roles'),
                'token' => $token,
            ],
            'message' => __('messages.auth.company_registered'),
        ], 201);
    }

    /**
     * POST /api/auth/register-consumer
     * Registro público de un usuario final de la app de consumo.
     */
    public function registerConsumer(RegisterConsumerRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'company_id' => null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        $user->assignRole('consumer');

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => $user->load('roles'),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => __('messages.auth.logged_out')]);
    }

    /**
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __('messages.auth.reset_link_sent')])
            : response()->json(['message' => __('messages.auth.reset_link_failed')], 400);
    }

    /**
     * POST /api/auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => Hash::make($password)])->save();
        });

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __('messages.auth.password_updated')])
            : response()->json(['message' => __('messages.auth.invalid_reset_token')], 400);
    }

    /**
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->load('company', 'roles', 'permissions'),
        ]);
    }
}
