<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar unique constraint para slug por tenant em products
        Schema::table('products', function (Blueprint $table) {
            $table->unique(['tenant_id', 'slug']);
        });

        // Adicionar unique constraint para slug por tenant em categories
        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'slug']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'slug']);
        });
    }
};
