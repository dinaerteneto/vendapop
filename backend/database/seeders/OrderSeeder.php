<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Clientes fictícios por tenant (nome, email, telefone).
     */
    private array $tenantsConfig = [
        'modachic' => [
            'prefix' => 'MODA',
            'customers' => [
                ['name' => 'Ana Paula Ferreira', 'email' => 'ana.ferreira@example.com', 'phone' => '5511988887701'],
                ['name' => 'Bruna Silva Costa', 'email' => 'bruna.costa@example.com', 'phone' => '5511988887702'],
                ['name' => 'Camila Rodrigues', 'email' => 'camila.rodrigues@example.com', 'phone' => '5511988887703'],
                ['name' => 'Debora Almeida', 'email' => 'debora.almeida@example.com', 'phone' => '5511988887704'],
            ],
        ],
        'casa-lar-imoveis' => [
            'prefix' => 'IMOV',
            'customers' => [
                ['name' => 'Eduardo Martins', 'email' => 'eduardo.martins@example.com', 'phone' => '5511988887705'],
                ['name' => 'Fernanda Souza', 'email' => 'fernanda.souza@example.com', 'phone' => '5511988887706'],
                ['name' => 'Gustavo Lima', 'email' => 'gustavo.lima@example.com', 'phone' => '5511988887707'],
                ['name' => 'Helena Barbosa', 'email' => 'helena.barbosa@example.com', 'phone' => '5511988887708'],
            ],
        ],
        'techstore-brasil' => [
            'prefix' => 'TECH',
            'customers' => [
                ['name' => 'Igor Cardoso', 'email' => 'igor.cardoso@example.com', 'phone' => '5511988887709'],
                ['name' => 'Juliana Pereira', 'email' => 'juliana.pereira@example.com', 'phone' => '5511988887710'],
                ['name' => 'Kleber Nogueira', 'email' => 'kleber.nogueira@example.com', 'phone' => '5511988887711'],
                ['name' => 'Larissa Teixeira', 'email' => 'larissa.teixeira@example.com', 'phone' => '5511988887712'],
            ],
        ],
        'moda-fashion' => [
            'prefix' => 'FASH',
            'customers' => [
                ['name' => 'Marcelo Ribeiro', 'email' => 'marcelo.ribeiro@example.com', 'phone' => '5511988887713'],
                ['name' => 'Natalia Gomes', 'email' => 'natalia.gomes@example.com', 'phone' => '5511988887714'],
                ['name' => 'Otavio Correia', 'email' => 'otavio.correia@example.com', 'phone' => '5511988887715'],
                ['name' => 'Patricia Dias', 'email' => 'patricia.dias@example.com', 'phone' => '5511988887716'],
            ],
        ],
        'brilho-elegancia' => [
            'prefix' => 'JOIA',
            'customers' => [
                ['name' => 'Rafael Monteiro', 'email' => 'rafael.monteiro@example.com', 'phone' => '5511988887717'],
                ['name' => 'Sabrina Castro', 'email' => 'sabrina.castro@example.com', 'phone' => '5511988887718'],
                ['name' => 'Tiago Freitas', 'email' => 'tiago.freitas@example.com', 'phone' => '5511988887719'],
                ['name' => 'Vanessa Moraes', 'email' => 'vanessa.moraes@example.com', 'phone' => '5511988887720'],
            ],
        ],
        'confeitaria-artesanal' => [
            'prefix' => 'BOLO',
            'customers' => [
                ['name' => 'Wagner Azevedo', 'email' => 'wagner.azevedo@example.com', 'phone' => '5511988887721'],
                ['name' => 'Ximena Rocha', 'email' => 'ximena.rocha@example.com', 'phone' => '5511988887722'],
                ['name' => 'Yasmin Cunha', 'email' => 'yasmin.cunha@example.com', 'phone' => '5511988887723'],
                ['name' => 'Zeca Pinheiro', 'email' => 'zeca.pinheiro@example.com', 'phone' => '5511988887724'],
            ],
        ],
        'personaliza-facil' => [
            'prefix' => 'ENCO',
            'customers' => [
                ['name' => 'Alice Batista', 'email' => 'alice.batista@example.com', 'phone' => '5511988887725'],
                ['name' => 'Bernardo Duarte', 'email' => 'bernardo.duarte@example.com', 'phone' => '5511988887726'],
                ['name' => 'Cecilia Nunes', 'email' => 'cecilia.nunes@example.com', 'phone' => '5511988887727'],
                ['name' => 'Diego Farias', 'email' => 'diego.farias@example.com', 'phone' => '5511988887728'],
            ],
        ],
        'ofertas-do-dia' => [
            'prefix' => 'AFIL',
            'customers' => [
                ['name' => 'Elisa Campos', 'email' => 'elisa.campos@example.com', 'phone' => '5511988887729'],
                ['name' => 'Fabio Guimaraes', 'email' => 'fabio.guimaraes@example.com', 'phone' => '5511988887730'],
                ['name' => 'Gabriela Vieira', 'email' => 'gabriela.vieira@example.com', 'phone' => '5511988887731'],
                ['name' => 'Henrique Lopes', 'email' => 'henrique.lopes@example.com', 'phone' => '5511988887732'],
            ],
        ],
        'boa-massa' => [
            'prefix' => 'PIZZ',
            'customers' => [
                ['name' => 'Isabela Marques', 'email' => 'isabela.marques@example.com', 'phone' => '5511988887733'],
                ['name' => 'Joao Vitor Pinto', 'email' => 'joao.pinto@example.com', 'phone' => '5511988887734'],
                ['name' => 'Karina Melo', 'email' => 'karina.melo@example.com', 'phone' => '5511988887735'],
                ['name' => 'Leandro Sales', 'email' => 'leandro.sales@example.com', 'phone' => '5511988887736'],
            ],
        ],
        'auto-mecanica-do-ze' => [
            'prefix' => 'OFIC',
            'customers' => [
                ['name' => 'Mauricio Tavares', 'email' => 'mauricio.tavares@example.com', 'phone' => '5511988887737'],
                ['name' => 'Nicole Fontes', 'email' => 'nicole.fontes@example.com', 'phone' => '5511988887738'],
                ['name' => 'Osvaldo Reis', 'email' => 'osvaldo.reis@example.com', 'phone' => '5511988887739'],
                ['name' => 'Priscila Andrade', 'email' => 'priscila.andrade@example.com', 'phone' => '5511988887740'],
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->tenantsConfig as $slug => $config) {
            $tenant = Tenant::where('slug', $slug)->first();

            if (! $tenant) {
                continue;
            }

            $products = Product::where('tenant_id', $tenant->id)->inRandomOrder()->limit(10)->get();

            if ($products->isEmpty()) {
                continue;
            }

            $customers = collect($config['customers'])->map(function ($customerData) use ($tenant) {
                return Customer::firstOrCreate(
                    ['tenant_id' => $tenant->id, 'email' => $customerData['email']],
                    [
                        'tenant_id' => $tenant->id,
                        'name' => $customerData['name'],
                        'phone' => $customerData['phone'],
                    ]
                );
            });

            $statuses = OrderStatus::values();
            $orderCount = 6;

            for ($i = 1; $i <= $orderCount; $i++) {
                $orderNumber = "{$config['prefix']}-2026-".str_pad((string) $i, 6, '0', STR_PAD_LEFT);

                if (Order::where('tenant_id', $tenant->id)->where('order_number', $orderNumber)->exists()) {
                    continue;
                }

                $customer = $customers[($i - 1) % $customers->count()];
                $itemsCount = random_int(1, 3);
                $orderProducts = $products->random(min($itemsCount, $products->count()));
                $orderProducts = $orderProducts instanceof Product ? collect([$orderProducts]) : $orderProducts;

                $order = Order::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'order_number' => $orderNumber,
                    'total_amount' => 0,
                    'status' => $statuses[($i - 1) % count($statuses)],
                    'notes' => null,
                ]);

                $total = 0;

                foreach ($orderProducts as $product) {
                    $quantity = random_int(1, 3);
                    $subtotal = $product->price * $quantity;
                    $total += $subtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price' => $product->price,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal,
                    ]);
                }

                $order->update(['total_amount' => $total]);
            }
        }

        $this->command->info('Seeder de Orders criado com sucesso!');
    }
}
