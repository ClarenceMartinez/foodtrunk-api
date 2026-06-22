<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Food Trunk
|--------------------------------------------------------------------------
| Esta es la Fase 1: Arquitectura, Auth y Roles.
| Los módulos de negocio (Empresas, Food Trucks, Menús, Suscripciones,
| Pagos, etc.) se agregan aquí mismo en la siguiente fase, siguiendo
| el mismo patrón: Controller + FormRequest + Resource + middleware.
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register-company', [AuthController::class, 'registerCompany']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Rutas exclusivas del Platform Owner (super administrador).
Route::middleware(['auth:sanctum', 'role:platform-owner'])
    ->prefix('platform')
    ->group(function () {
        Route::get('/ping', fn () => response()->json(['message' => 'Platform Owner OK']));
        // Aquí se agregan: companies, plans (gestión global), platform reports...
    });

// Rutas para usuarios de una Empresa (company-admin / operator).
Route::middleware(['auth:sanctum', 'role:company-admin|operator'])
    ->prefix('company')
    ->group(function () {
        Route::get('/ping', fn () => response()->json(['message' => 'Company user OK']));
        // Aquí se agregan: food-trucks, locations, menus, promotions, subscriptions...
    });
