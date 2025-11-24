<?php

namespace Database\Seeders;

use App\Models\RotatingBanner;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class RotatingBannerSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'modachic')->first();

        if (!$tenant) {
            $this->command->warn('Tenant "modachic" não encontrado. Execute TenantSeeder primeiro.');
            return;
        }

        $banners = [
            [
                'title' => 'Coleção Verão 2025',
                'description' => 'Descubra as últimas tendências da moda feminina',
                'image_url' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Promoção Especial',
                'description' => 'Até 50% OFF em toda a loja',
                'image_url' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Novos Lançamentos',
                'description' => 'Confira as peças que acabaram de chegar',
                'image_url' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Frete Grátis',
                'description' => 'Em compras acima de R$ 200,00',
                'image_url' => 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Siga-nos no Instagram',
                'description' => 'Fique por dentro das novidades e promoções',
                'image_url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1200&h=600&fit=crop',
                'link_url' => 'https://instagram.com/modachic',
                'order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $bannerData) {
            RotatingBanner::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'image_url' => $bannerData['image_url'],
                ],
                array_merge($bannerData, [
                    'tenant_id' => $tenant->id,
                    'image_path' => null,
                    'is_external' => true,
                ])
            );
        }

        $this->command->info('5 banners criados para a loja modachic');
    }
}

