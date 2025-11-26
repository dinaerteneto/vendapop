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
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('business_sector', [
                'fashion',           // Roupas/Moda
                'electronics',       // Eletrônicos
                'jewelry',           // Joias
                'real_estate',       // Imobiliária
                'food',              // Bolos/Comida
                'custom_orders',     // Encomendas Personalizadas
                'affiliates',        // Afiliados
                'other'              // Outros
            ])->nullable()->after('description')->comment('Ramo de atividade do tenant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('business_sector');
        });
    }
};

