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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('path')->nullable();
            $table->boolean('is_external')->default(false);
            $table->boolean('is_main')->default(false);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'main_image_url')) {
                $table->dropColumn('main_image_url');
            }
            if (Schema::hasColumn('products', 'images')) {
                $table->dropColumn('images');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'main_image_url')) {
                $table->string('main_image_url')->nullable();
            }
            if (!Schema::hasColumn('products', 'images')) {
                $table->json('images')->nullable();
            }
        });

        Schema::dropIfExists('product_images');
    }
};
