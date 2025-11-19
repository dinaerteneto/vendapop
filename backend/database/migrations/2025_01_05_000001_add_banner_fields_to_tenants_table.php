<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('banner_message')->nullable()->after('description');
            $table->string('banner_text_color_1')->default('#ffffff')->after('banner_message');
            $table->string('banner_text_color_2')->default('#fbbf24')->after('banner_text_color_1'); // Default Yellow-400
            $table->string('banner_background_color')->default('#000000')->after('banner_text_color_2');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'banner_message',
                'banner_text_color_1',
                'banner_text_color_2',
                'banner_background_color'
            ]);
        });
    }
};

