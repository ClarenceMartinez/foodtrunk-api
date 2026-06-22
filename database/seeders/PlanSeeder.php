<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Plan gratuito para comenzar.',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'max_food_trucks' => 1,
                'features' => ['1 food truck', 'Soporte por email'],
            ],
            [
                'name' => 'Básico',
                'slug' => 'basico',
                'description' => 'Para empresas pequeñas.',
                'price' => 29,
                'billing_cycle' => 'monthly',
                'max_food_trucks' => 3,
                'features' => ['Hasta 3 food trucks', 'Promociones básicas', 'Soporte por email'],
            ],
            [
                'name' => 'Profesional',
                'slug' => 'profesional',
                'description' => 'Para empresas en crecimiento.',
                'price' => 79,
                'billing_cycle' => 'monthly',
                'max_food_trucks' => 5,
                'features' => ['Hasta 5 food trucks', 'Promociones ilimitadas', 'Reportes avanzados'],
            ],
            [
                'name' => 'Empresa',
                'slug' => 'empresa',
                'description' => 'Para operaciones a gran escala.',
                'price' => 199,
                'billing_cycle' => 'monthly',
                'max_food_trucks' => null,
                'features' => ['Food trucks ilimitados', 'Reportes avanzados', 'Soporte prioritario'],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
