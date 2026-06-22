<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Plataforma (solo Platform Owner)
            'manage-companies',
            'manage-plans',
            'view-platform-dashboard',
            'view-platform-reports',
            'manage-platform-users',

            // Gestión de negocio (Empresa)
            'manage-food-trucks',
            'manage-locations',
            'manage-menus',
            'manage-promotions',
            'manage-operators',

            // Suscripciones y pagos
            'manage-subscriptions',
            'view-billing',
            'manage-payment-methods',

            // Reportes
            'view-company-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Platform Owner: control total de la plataforma.
        $platformOwner = Role::firstOrCreate(['name' => 'platform-owner', 'guard_name' => 'web']);
        $platformOwner->syncPermissions(Permission::all());

        // Company Admin (Manager): administra su propia empresa.
        $companyAdmin = Role::firstOrCreate(['name' => 'company-admin', 'guard_name' => 'web']);
        $companyAdmin->syncPermissions([
            'manage-food-trucks',
            'manage-locations',
            'manage-menus',
            'manage-promotions',
            'manage-operators',
            'manage-subscriptions',
            'view-billing',
            'manage-payment-methods',
            'view-company-reports',
        ]);

        // Operator: opera un food truck puntual (uso futuro en app móvil).
        $operator = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $operator->syncPermissions([
            'manage-menus',
            'manage-locations',
        ]);
    }
}
