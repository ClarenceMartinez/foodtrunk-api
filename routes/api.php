<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DiscoverController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\FoodTruckController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\NearbySuggestionsController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\NotificationSettingsController;
use App\Http\Controllers\Api\OperatorController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PlatformFoodTruckController;
use App\Http\Controllers\Api\PlatformLocationController;
use App\Http\Controllers\Api\PlatformPromotionController;
use App\Http\Controllers\Api\PlatformSubscriptionController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserLocationController;
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
    Route::post('/register-consumer', [AuthController::class, 'registerConsumer']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Catálogo de planes: cualquier usuario autenticado puede consultarlo
// (Platform Owner para administrar, Company para elegir/comparar).
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/plans', [PlanController::class, 'index']);
    Route::get('/plans/{plan}', [PlanController::class, 'show']);
    Route::post('/uploads', [UploadController::class, 'store']);
});

// Directorio PÚBLICO para la Consumer App. No requiere autenticación —
// cualquiera puede explorar food trucks, ver su menú y promociones activas.
Route::prefix('discover')->group(function () {
    Route::get('/food-trucks', [DiscoverController::class, 'foodTrucks']);
    // IMPORTANTE: '/nearby' debe registrarse ANTES de '/{foodTruck}',
    // de lo contrario Laravel intentará interpretar "nearby" como un ID.
    Route::get('/food-trucks/nearby', [DiscoverController::class, 'nearby']);
    Route::get('/food-trucks/{foodTruck}', [DiscoverController::class, 'foodTruckDetail']);
    Route::get('/promotions', [DiscoverController::class, 'promotions']);
});

// Rutas del Consumer (usuario final de la app móvil) — requieren cuenta.
Route::middleware(['auth:sanctum', 'role:consumer'])
    ->prefix('consumer')
    ->group(function () {
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites/{foodTruck}', [FavoriteController::class, 'store']);
        Route::delete('/favorites/{foodTruck}', [FavoriteController::class, 'destroy']);
        // Favoritos = Follow (decision de producto: un solo corazon = seguir).
        Route::get('/favorites/{foodTruck}/status', [FavoriteController::class, 'status']);
        Route::patch('/favorites/{foodTruck}/notifications', [FavoriteController::class, 'updateNotifications']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    });

// Smart Nearby Alerts: preferencias, ubicacion y sugerencias del usuario.
// No restringido a role:consumer -- cualquier usuario autenticado puede
// tener sus propias preferencias de notificaciones.
Route::middleware('auth:sanctum')
    ->prefix('me')
    ->group(function () {
        Route::get('/notification-settings', [NotificationSettingsController::class, 'show']);
        Route::patch('/notification-settings', [NotificationSettingsController::class, 'update']);
        Route::post('/location', [UserLocationController::class, 'store']);
        Route::get('/nearby-suggestions', [NearbySuggestionsController::class, 'index']);
        // Registro/actualizacion del FCM token del dispositivo. La app
        // Flutter llama esto al iniciar sesion y cuando el token se refresca.
        Route::post('/fcm-token', [FcmTokenController::class, 'store']);
    });

// Rutas exclusivas del Platform Owner (super administrador).
Route::middleware(['auth:sanctum', 'role:platform-owner'])
    ->prefix('platform')
    ->group(function () {
        Route::get('/ping', fn () => response()->json(['message' => 'Platform Owner OK']));

        // Gestión global de empresas (tenants).
        Route::get('/companies', [CompanyController::class, 'index']);
        Route::post('/companies', [CompanyController::class, 'store']);
        Route::get('/companies/{company}', [CompanyController::class, 'show']);
        Route::put('/companies/{company}', [CompanyController::class, 'update']);
        Route::patch('/companies/{company}', [CompanyController::class, 'update']);
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy']);
        Route::post('/companies/{company}/approve', [CompanyController::class, 'approve']);
        Route::post('/companies/{company}/suspend', [CompanyController::class, 'suspend']);
        Route::post('/companies/{company}/reactivate', [CompanyController::class, 'reactivate']);

        // Gestión de planes (crear/editar/eliminar). El listado es público vía /api/plans.
        Route::post('/plans', [PlanController::class, 'store']);
        Route::put('/plans/{plan}', [PlanController::class, 'update']);
        Route::patch('/plans/{plan}', [PlanController::class, 'update']);
        Route::delete('/plans/{plan}', [PlanController::class, 'destroy']);

        // Gestión global de usuarios.
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::patch('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        // Vistas globales de solo lectura (Food Trucks, Ubicaciones, Promociones
        // de TODAS las empresas). La gestión/edición sigue siendo responsabilidad
        // de cada Company Admin vía /api/company/*.
        Route::get('/food-trucks', [PlatformFoodTruckController::class, 'index']);
        Route::get('/food-trucks/{foodTruck}', [PlatformFoodTruckController::class, 'show']);
        Route::get('/locations', [PlatformLocationController::class, 'index']);
        Route::get('/promotions', [PlatformPromotionController::class, 'index']);
        Route::get('/subscriptions', [PlatformSubscriptionController::class, 'index']);

        // Aquí se agregan: platform reports...
    });

// Rutas para usuarios de una Empresa (company-admin / operator).
Route::middleware(['auth:sanctum', 'role:company-admin|operator'])
    ->prefix('company')
    ->group(function () {
        Route::get('/ping', fn () => response()->json(['message' => 'Company user OK']));

        // Perfil de la propia empresa (no requiere ID, se resuelve por el usuario).
        Route::get('/profile', [CompanyController::class, 'myCompany']);
        Route::put('/profile', [CompanyController::class, 'updateMyCompany']);
        Route::patch('/profile', [CompanyController::class, 'updateMyCompany']);

        // Food Trucks (aislados automáticamente por empresa).
        Route::get('/food-trucks', [FoodTruckController::class, 'index']);
        Route::post('/food-trucks', [FoodTruckController::class, 'store']);
        Route::get('/food-trucks/{foodTruck}', [FoodTruckController::class, 'show']);
        Route::put('/food-trucks/{foodTruck}', [FoodTruckController::class, 'update']);
        Route::patch('/food-trucks/{foodTruck}', [FoodTruckController::class, 'update']);
        Route::delete('/food-trucks/{foodTruck}', [FoodTruckController::class, 'destroy']);

        // Ubicaciones (anidadas bajo un Food Truck).
        Route::get('/food-trucks/{foodTruck}/locations', [LocationController::class, 'index']);
        Route::post('/food-trucks/{foodTruck}/locations', [LocationController::class, 'store']);
        Route::get('/food-trucks/{foodTruck}/locations/{location}', [LocationController::class, 'show']);
        Route::put('/food-trucks/{foodTruck}/locations/{location}', [LocationController::class, 'update']);
        Route::patch('/food-trucks/{foodTruck}/locations/{location}', [LocationController::class, 'update']);
        Route::delete('/food-trucks/{foodTruck}/locations/{location}', [LocationController::class, 'destroy']);

        // Menús (anidados bajo un Food Truck).
        Route::get('/food-trucks/{foodTruck}/menus', [MenuController::class, 'index']);
        Route::post('/food-trucks/{foodTruck}/menus', [MenuController::class, 'store']);
        Route::get('/food-trucks/{foodTruck}/menus/{menu}', [MenuController::class, 'show']);
        Route::put('/food-trucks/{foodTruck}/menus/{menu}', [MenuController::class, 'update']);
        Route::patch('/food-trucks/{foodTruck}/menus/{menu}', [MenuController::class, 'update']);
        Route::delete('/food-trucks/{foodTruck}/menus/{menu}', [MenuController::class, 'destroy']);

        // Platillos (anidados bajo un Menú).
        Route::get('/food-trucks/{foodTruck}/menus/{menu}/items', [MenuItemController::class, 'index']);
        Route::post('/food-trucks/{foodTruck}/menus/{menu}/items', [MenuItemController::class, 'store']);
        Route::get('/food-trucks/{foodTruck}/menus/{menu}/items/{item}', [MenuItemController::class, 'show']);
        Route::put('/food-trucks/{foodTruck}/menus/{menu}/items/{item}', [MenuItemController::class, 'update']);
        Route::patch('/food-trucks/{foodTruck}/menus/{menu}/items/{item}', [MenuItemController::class, 'update']);
        Route::delete('/food-trucks/{foodTruck}/menus/{menu}/items/{item}', [MenuItemController::class, 'destroy']);

        // Promociones (a nivel de empresa, opcionalmente ligadas a un food truck).
        Route::get('/promotions', [PromotionController::class, 'index']);
        Route::post('/promotions', [PromotionController::class, 'store']);
        Route::get('/promotions/{promotion}', [PromotionController::class, 'show']);
        Route::put('/promotions/{promotion}', [PromotionController::class, 'update']);
        Route::patch('/promotions/{promotion}', [PromotionController::class, 'update']);
        Route::delete('/promotions/{promotion}', [PromotionController::class, 'destroy']);

        // Suscripciones de la empresa.
        Route::get('/subscription', [SubscriptionController::class, 'current']);
        Route::get('/subscriptions', [SubscriptionController::class, 'history']);
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);

        // Operadores de la empresa.
        Route::get('/operators', [OperatorController::class, 'index']);
        Route::post('/operators', [OperatorController::class, 'store']);
        Route::get('/operators/{operator}', [OperatorController::class, 'show']);
        Route::put('/operators/{operator}', [OperatorController::class, 'update']);
        Route::patch('/operators/{operator}', [OperatorController::class, 'update']);
        Route::delete('/operators/{operator}', [OperatorController::class, 'destroy']);
    });
