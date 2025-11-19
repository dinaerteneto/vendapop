<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\TenantSocial;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'modachic'],
            [
                'name' => 'Moda Chic',
                'whatsapp_number' => '5511999999999',
                'description' => 'A melhor moda feminina da região!',
                'primary_color' => '#7e22ce',
                'secondary_color' => '#f3e8ff',
                'address' => 'Rua da Moda, 123 - Centro, São Paulo/SP',
                'email_contact' => 'contato@modachic.com',
            ]
        );

        TenantSocial::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Instagram'],
            ['url' => 'https://instagram.com/modachic', 'icon' => 'instagram']
        );

        TenantSocial::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Facebook'],
            ['url' => 'https://facebook.com/modachic', 'icon' => 'facebook']
        );
    }
}

