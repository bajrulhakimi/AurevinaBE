<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            if (!Schema::hasColumn('promos', 'promo_code')) {
                $table->string('promo_code')->unique()->after('id');
            }
            if (!Schema::hasColumn('promos', 'discount_type')) {
                $table->enum('discount_type', ['percent', 'fixed'])->default('percent')->after('promo_code');
            }
            if (!Schema::hasColumn('promos', 'discount_value')) {
                $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('promos', 'start_date')) {
                $table->date('start_date')->nullable()->after('discount_value');
            }
            if (!Schema::hasColumn('promos', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('promos', function (Blueprint $table) {
            foreach (['promo_code', 'discount_type', 'discount_value', 'start_date', 'end_date'] as $column) {
                if (Schema::hasColumn('promos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
