<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\RotatingBanner;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ProductAttributeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OficinaMecanicaSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'auto-mecanica-do-ze'],
            [
                'name' => 'Auto Mecânica do Zé',
                'slug' => 'auto-mecanica-do-ze',
                'whatsapp_number' => '5511987654333',
                'description' => 'Mecânica de confiança desde 1998. Serviços rápidos, preço justo e garantia de 90 dias nas peças.',
                'banner_message' => '🔧 TROCA DE ÓLEO COM 20% OFF NESTA SEMANA! 🔧',
                'banner_text_color_1' => '#ffffff',
                'banner_text_color_2' => '#FFD700',
                'banner_background_color' => '#1a1a2e',
                'primary_color' => '#e94560',
                'secondary_color' => '#1a1a2e',
                'address' => 'Av. dos Automóveis, 1500 - Vila das Oficinas, São Paulo/SP',
                'email_contact' => 'contato@automecanicadoze.com.br',
                'business_sector' => 'custom_orders',
            ]
        );

        $attributeService = app(ProductAttributeService::class);
        $attributeService->createDefaultAttributesForSector($tenant, 'custom_orders');

        User::updateOrCreate(
            ['email' => 'admin@automecanicadoze.com.br'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Zé da Oficina',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'email_verified_at' => now(),
            ]
        );

        $catMotor = Category::updateOrCreate(
            ['slug' => 'servicos-de-motor', 'tenant_id' => $tenant->id],
            ['name' => 'Serviços de Motor', 'is_active' => true, 'image_url' => 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=500&h=500&fit=crop']
        );

        $catFreios = Category::updateOrCreate(
            ['slug' => 'freios-e-suspensao', 'tenant_id' => $tenant->id],
            ['name' => 'Freios e Suspensão', 'is_active' => true, 'image_url' => 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?w=500&h=500&fit=crop']
        );

        $catPneus = Category::updateOrCreate(
            ['slug' => 'pneus-e-rodas', 'tenant_id' => $tenant->id],
            ['name' => 'Pneus e Rodas', 'is_active' => true, 'image_url' => 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?w=500&h=500&fit=crop']
        );

        $catEletrica = Category::updateOrCreate(
            ['slug' => 'eletrica-e-ar-condicionado', 'tenant_id' => $tenant->id],
            ['name' => 'Elétrica e Ar Condicionado', 'is_active' => true, 'image_url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=500&h=500&fit=crop']
        );

        $this->createServicosMotor($tenant, $catMotor);
        $this->createFreiosSuspensao($tenant, $catFreios);
        $this->createPneusRodas($tenant, $catPneus);
        $this->createEletricaAr($tenant, $catEletrica);
        $this->createBanners($tenant);

        $this->command->info('Seeder da Auto Mecânica do Zé criado com sucesso!');
    }

    private function createServicosMotor(Tenant $tenant, Category $cat): void
    {
        $servicos = [
            [
                'name' => 'Troca de Óleo + Filtro',
                'short_description' => 'Óleo sintético, semi ou mineral + filtro de óleo novo',
                'description' => "Troca completa de óleo do motor com filtro de óleo incluso.\n\nOpções de óleo:\n• Mineral 15W-40 (básico)\n• Semi-sintético 10W-40\n• Sintético 5W-30 (alto desempenho)\n\nInclui: dreno do óleo velho, troca do filtro, verificação de nível e descarte ecológico.\nTempo estimado: 30 min.",
                'price' => 89.90,
                'action_type' => 'add_to_cart',
            ],
            [
                'name' => 'Troca de Correia Dentada',
                'short_description' => 'Kit correia dentada + tensor. Preventivo essencial.',
                'description' => "Troca da correia dentada do motor com tensor incluso.\n\nFundamental para evitar danos graves ao motor. Recomendado a cada 50.000 km ou conforme manual do fabricante.\n\nTempo estimado: 2 a 4 horas.",
                'price' => 399.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de agendar a troca da correia dentada. Pode verificar disponibilidade?',
            ],
            [
                'name' => 'Limpeza de Bicos Injetores',
                'short_description' => 'Limpeza ultrassônica dos bicos injetores',
                'description' => "Limpeza profissional dos bicos injetores com equipamento ultrassônico.\n\nMelhora a queima de combustível, reduz consumo e falhas de marcha lenta.\n\nTempo estimado: 1 a 2 horas.",
                'price' => 149.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de agendar uma limpeza de bicos injetores. Tem horário disponível?',
            ],
            [
                'name' => 'Troca de Velas e Cabos',
                'short_description' => 'Kit velas de irídio + cabos de ignição',
                'description' => "Troca completa das velas de ignição e cabos.\n\nVelas de irídio de alta durabilidade. Melhora partida a frio e economia de combustível.\n\nTempo estimado: 45 min.",
                'price' => 159.90,
                'action_type' => 'add_to_cart',
            ],
            [
                'name' => 'Revisão Completa de 50.000 km',
                'short_description' => 'Checklist completo: óleo, filtros, correias, velas, freios e fluidos',
                'description' => "Revisão preventiva completa conforme plano de manutenção do fabricante.\n\nItens verificados:\n• Óleo do motor e filtro\n• Filtro de ar e combustível\n• Correia dentada e alternador\n• Velas de ignição\n• Pastilhas e discos de freio\n• Fluido de freio, arrefecimento e direção\n• Bateria e alternador\n• Suspensão e amortecedores\n\nTempo estimado: 3 a 5 horas.",
                'price' => 599.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de agendar a revisão de 50.000 km. Qual a melhor data?',
            ],
        ];

        $this->createProducts($tenant, $cat, $servicos, 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=800&h=600&fit=crop');
    }

    private function createFreiosSuspensao(Tenant $tenant, Category $cat): void
    {
        $servicos = [
            [
                'name' => 'Troca de Pastilhas de Freio',
                'short_description' => 'Pastilhas cerâmicas dianteiras ou traseiras. Parcelamos!',
                'description' => "Troca de pastilhas de freio com material cerâmico de alta durabilidade.\n\nDisponível para eixo dianteiro, traseiro ou ambos.\nInclui limpeza do sistema e verificação do disco de freio.\n\nTempo estimado: 40 min por eixo.",
                'price' => 129.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Preciso trocar as pastilhas de freio. Pode me dar um orçamento?',
            ],
            [
                'name' => 'Troca de Discos de Freio',
                'short_description' => 'Discos ventilados dianteiros ou traseiros',
                'description' => "Troca completa dos discos de freio.\n\nDiscos ventilados de alto desempenho. Recomendado trocar pastilhas junto.\nInclui regulagem e teste de frenagem.\n\nTempo estimado: 1h30 por eixo.",
                'price' => 249.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Preciso trocar os discos de freio. Pode verificar e me passar um orçamento?',
            ],
            [
                'name' => 'Troca de Amortecedores',
                'short_description' => 'Amortecedores dianteiros ou traseiros. Parcelamos em até 10x!',
                'description' => "Troca de amortecedores com alinhamento incluso.\n\nAmortecedores pressurizados de primeira linha.\nKit completo: amortecedor + coxim + batente + protetor.\n\nTempo estimado: 2 horas por eixo.",
                'price' => 349.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de um orçamento para troca de amortecedores. Pode me ajudar?',
            ],
            [
                'name' => 'Revisão Completa de Freios',
                'short_description' => 'Verificação de pastilhas, discos, fluido e cilindros',
                'description' => "Diagnóstico completo do sistema de freios:\n• Medição de espessura de pastilhas e discos\n• Verificação de fluido de freio (nível e contaminação)\n• Inspeção de cilindros e mangueiras\n• Teste de frenagem\n\nRelatório detalhado incluso. Tempo estimado: 1 hora.",
                'price' => 49.90,
                'action_type' => 'add_to_cart',
            ],
        ];

        $this->createProducts($tenant, $cat, $servicos, 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?w=800&h=600&fit=crop');
    }

    private function createPneusRodas(Tenant $tenant, Category $cat): void
    {
        $servicos = [
            [
                'name' => 'Alinhamento Computadorizado',
                'short_description' => 'Alinhamento 3D de precisão. Evita desgaste irregular.',
                'description' => "Alinhamento computadorizado 3D de alta precisão.\n\nCorrige cambagem, caster e convergência.\nEvita desgaste irregular dos pneus e melhora a dirigibilidade.\n\nTempo estimado: 30 min.",
                'price' => 89.90,
                'action_type' => 'add_to_cart',
            ],
            [
                'name' => 'Balanceamento das 4 Rodas',
                'short_description' => 'Balanceamento eletrônico com contrapesos adesivos',
                'description' => "Balanceamento eletrônico das 4 rodas.\n\nContrapesos adesivos (não danificam a roda).\nElimina vibração no volante em altas velocidades.\n\nTempo estimado: 40 min.",
                'price' => 79.90,
                'action_type' => 'add_to_cart',
            ],
            [
                'name' => 'Pneu Remold H, Aro 13',
                'short_description' => 'Pneu 175/70 R13 remold com garantia de 2 anos',
                'description' => "Pneu remold H 175/70 R13.\n\nCertificado pelo INMETRO. Garantia de 2 anos contra defeitos de fabricação.\nPreço por unidade. Montagem e balanceamento à parte.\n\nTempo estimado de instalação: 20 min por pneu.",
                'price' => 179.90,
                'action_type' => 'add_to_cart',
            ],
            [
                'name' => 'Pneu Novo Aro 14',
                'short_description' => 'Pneu 175/65 R14 primeira linha com garantia de 5 anos',
                'description' => "Pneu novo 175/65 R14 de primeira linha.\n\nMarca premium com garantia de 5 anos.\nExcelente aderência em piso molhado e baixo ruído de rodagem.\nPreço por unidade. Montagem e balanceamento inclusos.\n\nTempo estimado: 25 min por pneu.",
                'price' => 349.90,
                'action_type' => 'add_to_cart',
            ],
            [
                'name' => 'Pneu Novo Aro 15',
                'short_description' => 'Pneu 195/55 R15 esportivo com garantia de 5 anos',
                'description' => "Pneu novo 195/55 R15 perfil esportivo.\n\nDesempenho superior em curvas, ótima aderência.\nPreço por unidade. Montagem e balanceamento inclusos.\n\nTempo estimado: 25 min por pneu.",
                'price' => 429.90,
                'action_type' => 'add_to_cart',
            ],
            [
                'name' => 'Combo Alinhamento + Balanceamento',
                'short_description' => 'Alinhamento 3D + balanceamento das 4 rodas com desconto!',
                'description' => "Pacote completo: alinhamento computadorizado 3D + balanceamento eletrônico das 4 rodas.\n\nEconomize em relação aos serviços avulsos.\nRecomendado a cada 10.000 km ou após troca de pneus.\n\nTempo estimado: 1 hora.",
                'price' => 139.90,
                'action_type' => 'add_to_cart',
            ],
        ];

        $this->createProducts($tenant, $cat, $servicos, 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?w=800&h=600&fit=crop');
    }

    private function createEletricaAr(Tenant $tenant, Category $cat): void
    {
        $servicos = [
            [
                'name' => 'Diagnóstico de Injeção Eletrônica',
                'short_description' => 'Scanner automotivo profissional. Leitura e limpeza de falhas.',
                'description' => "Diagnóstico completo com scanner automotivo profissional.\n\n• Leitura de códigos de falha (OBD2)\n• Análise de sensores e atuadores\n• Limpeza da memória de falhas\n• Relatório impresso detalhado\n\nTempo estimado: 30 min.",
                'price' => 79.90,
                'action_type' => 'add_to_cart',
            ],
            [
                'name' => 'Recarga de Ar Condicionado',
                'short_description' => 'Gás R-134a ou R-1234yf. Verificação de vazamentos.',
                'description' => "Recarga completa do sistema de ar condicionado.\n\n• Verificação de vazamentos com nitrogênio\n• Vácuo no sistema\n• Carga de gás refrigerante (R-134a ou R-1234yf)\n• Óleo lubrificante para compressor\n• Teste de temperatura na saída do difusor\n\nTempo estimado: 1 hora.",
                'price' => 199.90,
                'action_type' => 'add_to_cart',
            ],
            [
                'name' => 'Troca de Bateria',
                'short_description' => 'Baterias Moura, Heliar, Bosch. Instalação na hora.',
                'description' => "Troca de bateria com instalação inclusa.\n\n• Teste de carga da bateria atual\n• Bateria nova com garantia de fábrica (18 a 24 meses)\n• Verificação do alternador e sistema de carga\n• Descarte ecológico da bateria velha\n\nTempo estimado: 20 min.",
                'price' => 299.90,
                'action_type' => 'add_to_cart',
            ],
            [
                'name' => 'Instalação de Som Automotivo',
                'short_description' => 'Instalação de rádio, alto-falantes, subwoofer e amplificador',
                'description' => "Instalação profissional de equipamentos de som.\n\n• Rádio multimídia (DVD, Bluetooth, espelhamento)\n• Alto-falantes e tweeters\n• Subwoofer e amplificador\n• Cabeamento dedicado e fusíveis de proteção\n\nMão de obra somente. Equipamentos à parte ou sob consulta.\n\nTempo estimado: 2 a 4 horas dependendo da complexidade.",
                'price' => 249.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Gostaria de instalar um som no carro. Podemos conversar sobre os equipamentos?',
            ],
            [
                'name' => 'Troca de Alternador',
                'short_description' => 'Alternador novo ou recondicionado com garantia',
                'description' => "Troca de alternador com teste de carga incluso.\n\n• Alternador novo ou recondicionado\n• Garantia de 6 meses\n• Teste de carga da bateria e sistema elétrico\n\nTempo estimado: 1 a 2 horas.",
                'price' => 449.90,
                'action_type' => 'whatsapp_contact',
                'whatsapp_message' => 'Olá! Preciso trocar o alternador do meu carro. Pode verificar o modelo e preço?',
            ],
        ];

        $this->createProducts($tenant, $cat, $servicos, 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&h=600&fit=crop');
    }

    private function createProducts(Tenant $tenant, Category $cat, array $servicos, string $defaultImage): void
    {
        static $imageIdx = 0;
        $variedImages = [
            ['https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=800&h=600&fit=crop', 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=800&h=600&fit=crop'],
            ['https://images.unsplash.com/photo-1487754180451-c456f719a1fc?w=800&h=600&fit=crop', 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=800&h=600&fit=crop'],
            ['https://images.unsplash.com/photo-1580273916550-e323be2ae537?w=800&h=600&fit=crop', 'https://images.unsplash.com/photo-1493238792000-8113da705763?w=800&h=600&fit=crop'],
            ['https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=800&h=600&fit=crop', 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=800&h=600&fit=crop'],
        ];

        $idx = 0;
        foreach ($servicos as $data) {
            $urls = $variedImages[$imageIdx % count($variedImages)];
            $imageIdx++;
            $mainImage = $data['main_image'] ?? $urls[0];
            $gallery = $data['gallery_images'] ?? $urls;
            $whatsapp = $data['whatsapp_message'] ?? null;
            unset($data['main_image'], $data['gallery_images']);

            $product = Product::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($data['name']), 'tenant_id' => $tenant->id],
                array_merge($data, [
                    'tenant_id' => $tenant->id,
                    'category_id' => $cat->id,
                    'whatsapp_message' => $whatsapp,
                    'is_active' => true,
                    'is_hot' => false,
                ])
            );

            $this->syncImages($product, $mainImage, $gallery);
        }
    }

    private function syncImages(Product $product, string $mainUrl, array $galleryUrls): void
    {
        $product->images()->delete();

        $product->images()->create([
            'url' => $mainUrl,
            'is_external' => true,
            'is_main' => true,
        ]);

        foreach ($galleryUrls as $url) {
            $product->images()->create([
                'url' => $url,
                'is_external' => true,
                'is_main' => false,
            ]);
        }
    }

    private function createBanners(Tenant $tenant): void
    {
        $banners = [
            [
                'title' => 'Troca de Óleo com Desconto',
                'description' => '20% OFF na troca de óleo + filtro esta semana. Agende já!',
                'image_url' => 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 0,
                'is_active' => true,
            ],
            [
                'title' => 'Alinhamento e Balanceamento',
                'description' => 'Combo com preço especial. Cuide dos seus pneus!',
                'image_url' => 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Revisão Preventiva',
                'description' => 'Seu carro merece cuidado. Parcelamos em até 10x!',
                'image_url' => 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?w=1200&h=600&fit=crop',
                'link_url' => null,
                'order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $bannerData) {
            RotatingBanner::updateOrCreate(
                ['tenant_id' => $tenant->id, 'image_url' => $bannerData['image_url']],
                array_merge($bannerData, [
                    'tenant_id' => $tenant->id,
                    'image_path' => null,
                    'is_external' => true,
                ])
            );
        }
    }
}
