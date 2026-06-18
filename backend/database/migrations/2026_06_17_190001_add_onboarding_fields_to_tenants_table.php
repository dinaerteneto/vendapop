<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('onboarding_completed')->default(false)->after('business_sector');
            $table->tinyInteger('onboarding_step')->default(0)->after('onboarding_completed');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['onboarding_completed', 'onboarding_step']);
        });
    }
};
