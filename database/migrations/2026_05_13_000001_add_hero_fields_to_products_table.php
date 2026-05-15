<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('show_on_hero')->default(false)->after('status');
            $table->unsignedTinyInteger('hero_position')->nullable()->after('show_on_hero');
            $table->index(['show_on_hero', 'hero_position']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['show_on_hero', 'hero_position']);
            $table->dropColumn(['show_on_hero', 'hero_position']);
        });
    }
};
