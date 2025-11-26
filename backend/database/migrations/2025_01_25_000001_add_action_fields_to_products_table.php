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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('action_type', ['add_to_cart', 'affiliate_link', 'whatsapp_contact'])
                ->default('add_to_cart')
                ->after('is_hot');
            $table->string('affiliate_link')->nullable()->after('action_type');
            $table->text('whatsapp_message')->nullable()->after('affiliate_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['action_type', 'affiliate_link', 'whatsapp_message']);
        });
    }
};

