<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spot_batches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('total_spots');
            $table->unsignedInteger('used_spots')->default(0);
            $table->string('batch_label')->nullable();
            $table->timestamp('replenishes_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spot_batches');
    }
};
