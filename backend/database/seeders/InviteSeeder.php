<?php

namespace Database\Seeders;

use App\Models\Invite;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class InviteSeeder extends Seeder
{
    public function run(): void
    {
        $modaChic = Tenant::where('slug', 'modachic')->first();

        // 1. Convite manual do admin (sem tenant associado) — Premium vitalício
        Invite::firstOrCreate(
            ['code' => 'ADMIN001'],
            [
                'type' => 'manual',
                'created_by_tenant_id' => null,
                'max_uses' => 1,
                'current_uses' => 0,
                'expires_at' => now()->addDays(30),
            ]
        );

        // 2. Convites de founder (Moda Chic) — 60 dias trial
        if ($modaChic) {
            Invite::firstOrCreate(
                ['code' => 'CHIC001'],
                [
                    'type' => 'manual',
                    'created_by_tenant_id' => $modaChic->id,
                    'max_uses' => 1,
                    'current_uses' => 0,
                    'expires_at' => now()->addDays(7),
                ]
            );

            Invite::firstOrCreate(
                ['code' => 'CHIC002'],
                [
                    'type' => 'manual',
                    'created_by_tenant_id' => $modaChic->id,
                    'max_uses' => 1,
                    'current_uses' => 0,
                    'expires_at' => now()->addDays(7),
                ]
            );
        }

        // 3. Link público com 3 vagas — 60 dias trial
        Invite::firstOrCreate(
            ['code' => 'BETA2026'],
            [
                'type' => 'public',
                'created_by_tenant_id' => null,
                'max_uses' => 3,
                'current_uses' => 0,
                'expires_at' => now()->addHours(72),
            ]
        );

        // 4. Link público com 5 vagas — 60 dias trial
        Invite::firstOrCreate(
            ['code' => 'VAGAS05'],
            [
                'type' => 'public',
                'created_by_tenant_id' => null,
                'max_uses' => 5,
                'current_uses' => 0,
                'expires_at' => now()->addHours(48),
            ]
        );

        // 5. Convite expirado (para testar validação)
        Invite::firstOrCreate(
            ['code' => 'EXPIRED1'],
            [
                'type' => 'manual',
                'created_by_tenant_id' => null,
                'max_uses' => 1,
                'current_uses' => 0,
                'expires_at' => now()->subDay(),
            ]
        );

        // 6. Convite esgotado (para testar mensagem "vagas esgotadas")
        Invite::firstOrCreate(
            ['code' => 'CHEIO01'],
            [
                'type' => 'public',
                'created_by_tenant_id' => null,
                'max_uses' => 2,
                'current_uses' => 2,
                'expires_at' => now()->addDays(7),
            ]
        );

        echo "Convites criados:\n";
        echo "  ADMIN001 (manual/admin - vitalício)\n";
        echo "  CHIC001, CHIC002 (founder/Moda Chic - 60 dias)\n";
        echo "  BETA2026 (público, 3 vagas - 72h)\n";
        echo "  VAGAS05 (público, 5 vagas - 48h)\n";
        echo "  EXPIRED1 (expirado)\n";
        echo "  CHEIO01 (esgotado)\n";
    }
}
