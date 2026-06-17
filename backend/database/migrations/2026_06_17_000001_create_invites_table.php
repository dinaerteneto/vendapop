<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->enum('type', ['manual', 'public']);
            $table->foreignId('created_by_tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->integer('max_uses')->default(1);
            $table->integer('current_uses')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invites');
    }
};
