<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@' . parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST)],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'tenant_id' => null,
                'email_verified_at' => now(),
            ]
        );
    }
}
