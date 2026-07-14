<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TenantSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            RotatingBannerSeeder::class,
            // Seeders dos novos ramos
            ImobiliariaSeeder::class,
            EletronicosSeeder::class,
            RoupasSeeder::class,
            JoiasSeeder::class,
            BoloCaseiroSeeder::class,
            EncomendasSeeder::class,
            AfiliadosSeeder::class,
            PizzariaSeeder::class,
            OrderSeeder::class,
            InviteSeeder::class,
            SuperAdminSeeder::class,
        ]);
    }
}
