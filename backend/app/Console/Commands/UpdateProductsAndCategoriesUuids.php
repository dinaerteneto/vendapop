<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateProductsAndCategoriesUuids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-uuids 
                            {--dry-run : Executar sem fazer alterações no banco}
                            {--force : Forçar atualização mesmo se já tiver UUID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza UUIDs para produtos, categorias e clientes que não possuem';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->info('🔍 Modo DRY-RUN: Nenhuma alteração será feita no banco de dados.');
            $this->newLine();
        }

        $this->info('🔄 Iniciando atualização de UUIDs...');
        $this->newLine();

        // Atualizar Products
        $this->updateProducts($dryRun, $force);

        // Atualizar Categories
        $this->updateCategories($dryRun, $force);

        // Atualizar Customers
        $this->updateCustomers($dryRun, $force);

        $this->newLine();
        $this->info('✅ Processo concluído!');

        return Command::SUCCESS;
    }

    /**
     * Atualiza UUIDs dos produtos
     */
    private function updateProducts(bool $dryRun, bool $force): void
    {
        $query = Product::query();
        
        if (!$force) {
            $query->whereNull('uuid');
        }

        $products = $query->get();
        $total = $products->count();

        if ($total === 0) {
            $this->info('📦 Produtos: Nenhum produto precisa de atualização.');
            return;
        }

        $this->info("📦 Produtos: {$total} produto(s) encontrado(s) para atualização.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        foreach ($products as $product) {
            if (!$dryRun) {
                $product->uuid = (string) Str::uuid();
                $product->save();
                $updated++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info("   → {$total} produto(s) seriam atualizados.");
        } else {
            $this->info("   → {$updated} produto(s) atualizado(s) com sucesso.");
        }
    }

    /**
     * Atualiza UUIDs das categorias
     */
    private function updateCategories(bool $dryRun, bool $force): void
    {
        $query = Category::query();
        
        if (!$force) {
            $query->whereNull('uuid');
        }

        $categories = $query->get();
        $total = $categories->count();

        if ($total === 0) {
            $this->info('📁 Categorias: Nenhuma categoria precisa de atualização.');
            return;
        }

        $this->info("📁 Categorias: {$total} categoria(s) encontrada(s) para atualização.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        foreach ($categories as $category) {
            if (!$dryRun) {
                $category->uuid = (string) Str::uuid();
                $category->save();
                $updated++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info("   → {$total} categoria(s) seriam atualizadas.");
        } else {
            $this->info("   → {$updated} categoria(s) atualizada(s) com sucesso.");
        }
    }

    /**
     * Atualiza UUIDs dos clientes
     */
    private function updateCustomers(bool $dryRun, bool $force): void
    {
        $query = Customer::query();
        
        if (!$force) {
            $query->whereNull('uuid');
        }

        $customers = $query->get();
        $total = $customers->count();

        if ($total === 0) {
            $this->info('👤 Clientes: Nenhum cliente precisa de atualização.');
            return;
        }

        $this->info("👤 Clientes: {$total} cliente(s) encontrado(s) para atualização.");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;
        foreach ($customers as $customer) {
            if (!$dryRun) {
                $customer->uuid = (string) Str::uuid();
                $customer->save();
                $updated++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info("   → {$total} cliente(s) seriam atualizados.");
        } else {
            $this->info("   → {$updated} cliente(s) atualizado(s) com sucesso.");
        }
    }
}
