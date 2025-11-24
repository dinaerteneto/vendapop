<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->unique()->nullable();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->unique()->nullable();
        });

        // Popular UUIDs para registros existentes
        DB::table('products')->whereNull('uuid')->get()->each(function ($product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update(['uuid' => (string) Str::uuid()]);
        });

        DB::table('categories')->whereNull('uuid')->get()->each(function ($category) {
            DB::table('categories')
                ->where('id', $category->id)
                ->update(['uuid' => (string) Str::uuid()]);
        });

        // Tornar UUID obrigatório
        Schema::table('products', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
