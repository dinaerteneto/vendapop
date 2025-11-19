<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Tenant;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'modachic')->first();

        if ($tenant) {
            Category::firstOrCreate(
                ['slug' => 'vestidos', 'tenant_id' => $tenant->id],
                ['name' => 'Vestidos', 'is_active' => true]
            );

            Category::firstOrCreate(
                ['slug' => 'blusas', 'tenant_id' => $tenant->id],
                ['name' => 'Blusas', 'is_active' => true]
            );

            Category::firstOrCreate(
                ['slug' => 'calcas', 'tenant_id' => $tenant->id],
                ['name' => 'Calças', 'is_active' => true]
            );
        }
    }
}

