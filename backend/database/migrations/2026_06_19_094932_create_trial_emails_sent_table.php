<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trial_emails_sent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('email_day');
            $table->timestamp('sent_at');
            $table->unique(['tenant_id', 'subscription_id', 'email_day'], 'uq_trial_email_sent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trial_emails_sent');
    }
};
