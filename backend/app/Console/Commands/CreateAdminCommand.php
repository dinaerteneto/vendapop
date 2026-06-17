<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    protected $signature = 'vendapop:admin';
    protected $description = 'Create the VendaPop admin tenant and user';

    public function handle(): int
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'vendapop'],
            [
                'name' => 'VendaPop Admin',
                'whatsapp_number' => '5511999999999',
                'primary_color' => '#7c3aed',
                'secondary_color' => '#f3e8ff',
            ]
        );

        $email = 'admin@' . parse_url(config('app.url'), PHP_URL_HOST);
        $password = $this->secret('Enter admin password (min 8 chars)');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Admin',
                'password' => Hash::make($password),
                'is_owner' => true,
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
            ]
        );

        $this->info("Admin tenant: {$tenant->slug}");
        $this->info("Admin user: {$email}");
        $this->info("Login at: /admin/login");
        $this->info("Dashboard: /admin");

        return self::SUCCESS;
    }
}
