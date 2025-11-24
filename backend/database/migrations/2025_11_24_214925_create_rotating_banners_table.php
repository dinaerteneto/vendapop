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
        Schema::create('rotating_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('image_url'); // URL da imagem (pode ser externa ou local)
            $table->string('image_path')->nullable(); // Caminho no storage se for upload local
            $table->boolean('is_external')->default(false); // true = URL externa, false = upload local
            $table->string('link_url')->nullable(); // Link opcional ao clicar no banner
            $table->integer('order')->default(0); // Ordem de exibição
            $table->boolean('is_active')->default(true);
            $table->string('title')->nullable(); // Título opcional do banner
            $table->text('description')->nullable(); // Descrição opcional
            $table->timestamps();

            $table->index(['tenant_id', 'is_active', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rotating_banners');
    }
};
