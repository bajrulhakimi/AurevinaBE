<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('special_price', 10, 2)->nullable()->after('base_price');
            $table->date('special_start_date')->nullable()->after('special_price');
            $table->date('special_end_date')->nullable()->after('special_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['special_price', 'special_start_date', 'special_end_date']);
        });
    }
};
