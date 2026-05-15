<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_gateway')->nullable()->after('payment_method');
            $table->string('gateway_order_id')->nullable()->after('payment_gateway');
            $table->string('gateway_transaction_id')->nullable()->after('gateway_order_id');
            $table->string('gateway_payment_url')->nullable()->after('gateway_transaction_id');
            $table->json('gateway_payload')->nullable()->after('gateway_payment_url');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'gateway_order_id',
                'gateway_transaction_id',
                'gateway_payment_url',
                'gateway_payload',
            ]);
        });
    }
};
