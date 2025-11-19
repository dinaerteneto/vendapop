<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'modachic')->first();

        if ($tenant) {
            User::firstOrCreate(
                ['email' => 'admin@modachic.com'],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Admin Moda Chic',
                    'password' => Hash::make('password'),
                    'is_owner' => true,
                ]
            );
        }
    }
}

