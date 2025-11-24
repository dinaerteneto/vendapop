<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->unique()->nullable();
        });

        // Popular UUIDs para registros existentes
        DB::table('customers')->whereNull('uuid')->get()->each(function ($customer) {
            DB::table('customers')
                ->where('id', $customer->id)
                ->update(['uuid' => (string) Str::uuid()]);
        });

        // Tornar UUID obrigatório
        Schema::table('customers', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
