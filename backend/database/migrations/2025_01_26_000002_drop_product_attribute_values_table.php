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
        Schema::dropIfExists('product_attribute_values');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recriar a tabela se necessário (não vamos fazer isso, mas deixar aqui para referência)
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('product_attributes')->onDelete('cascade');
            $table->string('value');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['attribute_id', 'is_active', 'order']);
        });
    }
};

