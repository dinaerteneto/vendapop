<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'modachic')->first();

        if ($tenant) {
            Category::updateOrCreate(
                ['slug' => 'vestidos', 'tenant_id' => $tenant->id],
                [
                    'name' => 'Vestidos',
                    'is_active' => true,
                    'image_url' => 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
                ]
            );

            Category::updateOrCreate(
                ['slug' => 'blusas', 'tenant_id' => $tenant->id],
                [
                    'name' => 'Blusas',
                    'is_active' => true,
                    'image_url' => 'https://images.unsplash.com/photo-1551163943-3f6a2b4ae78c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
                ]
            );

            Category::updateOrCreate(
                ['slug' => 'calcas', 'tenant_id' => $tenant->id],
                [
                    'name' => 'Calças',
                    'is_active' => true,
                    'image_url' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
                ]
            );

            Category::updateOrCreate(
                ['slug' => 'conjuntos', 'tenant_id' => $tenant->id],
                [
                    'name' => 'Conjuntos',
                    'is_active' => true,
                    'image_url' => 'https://images.unsplash.com/photo-1520591799316-6b30425429aa?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
                ]
            );

            Category::updateOrCreate(
                ['slug' => 'macaquinhos', 'tenant_id' => $tenant->id],
                [
                    'name' => 'Macaquinhos',
                    'is_active' => true,
                    'image_url' => 'https://images.unsplash.com/photo-1585487000160-6ebcfceb0d03?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60'
                ]
            );
        }
    }
}

