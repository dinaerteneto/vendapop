<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Atualizar valores antigos para o novo padrão
        DB::table('orders')->where('status', 'novo')->update(['status' => 'NEW']);
        DB::table('orders')->where('status', 'pendente')->update(['status' => 'NEW']);
        DB::table('orders')->where('status', 'concluído')->update(['status' => 'DONE']);
        DB::table('orders')->where('status', 'concluido')->update(['status' => 'DONE']);
        DB::table('orders')->where('status', 'cancelado')->update(['status' => 'CANCELED']);

        // Alterar coluna para aceitar apenas os valores do enum
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status')->default('NEW')->change();
        });
    }

    public function down(): void
    {
        // Reverter para valores antigos se necessário
        DB::table('orders')->where('status', 'NEW')->update(['status' => 'novo']);
        DB::table('orders')->where('status', 'DONE')->update(['status' => 'concluído']);
        DB::table('orders')->where('status', 'CANCELED')->update(['status' => 'cancelado']);
    }
};

