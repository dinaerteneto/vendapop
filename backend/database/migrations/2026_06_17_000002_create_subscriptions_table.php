<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->enum('plan_type', ['free', 'basic', 'professional', 'premium']);
            $table->enum('plan_status', ['active', 'trial', 'cancelled', 'expired']);
            $table->foreignId('invite_id')->nullable()->constrained('invites')->onDelete('set null');
            $table->enum('invite_source', ['manual', 'founder', 'public_link']);
            $table->timestamp('started_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'plan_status']);
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
