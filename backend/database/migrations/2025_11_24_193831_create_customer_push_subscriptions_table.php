<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('order_uuid'); // UUID do pedido
            $table->string('endpoint')->unique();
            $table->string('public_key');
            $table->string('auth_token');
            $table->timestamps();
            
            $table->index('order_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_push_subscriptions');
    }
};
