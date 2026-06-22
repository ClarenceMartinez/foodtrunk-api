<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformOwnerSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'owner@foodtrunk.app'],
            [
                'company_id' => null,
                'name' => 'Platform Owner',
                'password' => Hash::make('ChangeMe123!'),
                'status' => 'active',
            ]
        );

        if (! $owner->hasRole('platform-owner')) {
            $owner->assignRole('platform-owner');
        }
    }
}
