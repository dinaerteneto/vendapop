<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\TenantSocial;
use Illuminate\Database\Seeder;

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
                'primary_color' => '#6A040F', // Maroon
                'secondary_color' => '#FFF0F3',
                'address' => 'Rua da Moda, 123 - Centro, São Paulo/SP',
                'email_contact' => 'contato@modachic.com',
            ]
        );

        TenantSocial::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Instagram'],
            ['url' => 'https://instagram.com/modachic', 'icon' => 'https://cdn-icons-png.flaticon.com/512/2111/2111463.png']
        );

        TenantSocial::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Facebook'],
            ['url' => 'https://facebook.com/modachic', 'icon' => 'https://cdn-icons-png.flaticon.com/512/733/733547.png']
        );
    }
}

