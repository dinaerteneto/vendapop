<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('is_owner');
            $table->timestamp('last_login_at')->nullable()->after('is_super_admin');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('tenant_id')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_super_admin', 'last_login_at']);
        });
    }
};
