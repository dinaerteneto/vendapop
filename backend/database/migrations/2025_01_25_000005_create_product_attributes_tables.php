<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabela de atributos (ex: Tamanho, Cor, Tipo de Operação)
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name'); // ex: "Tamanho", "Cor", "Tipo de Operação"
            $table->string('slug');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active', 'order']);
            $table->unique(['tenant_id', 'slug']);
        });

        // Tabela de valores dos atributos (ex: P, M, G, Venda, Aluguel)
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('product_attributes')->onDelete('cascade');
            $table->string('value'); // ex: "P", "M", "Venda", "Aluguel"
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['attribute_id', 'is_active', 'order']);
        });

        // Tabela de variações (combinações de atributos com estoque, preço, SKU)
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->json('attributes'); // {"tamanho": "P", "cor": "Azul"}
            $table->integer('stock')->nullable();
            $table->decimal('price', 10, 2)->nullable(); // Preço específico da variação (opcional)
            $table->string('sku')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_attributes');
    }
};

