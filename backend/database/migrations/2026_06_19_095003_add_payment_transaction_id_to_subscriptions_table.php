<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('subscriptions', 'payment_transaction_id')) {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('payment_transaction_id')->nullable()->after('is_pending');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('payment_transaction_id');
        });
    }
};
