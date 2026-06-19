<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_limits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plan_type')->unique();
            $table->unsignedInteger('max_products')->default(0);
            $table->unsignedInteger('max_categories')->nullable()->default(null);
            $table->boolean('allow_custom_domain')->default(false);
            $table->boolean('allow_checkout_pix')->default(false);
            $table->boolean('allow_checkout_credit_card')->default(false);
            $table->boolean('allow_analytics')->default(false);
            $table->unsignedInteger('max_staff_accounts')->default(0);
            $table->unsignedInteger('max_orders_per_month')->nullable()->default(null);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_limits');
    }
};
