<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('address_id')->constrained('user_addresses')->onDelete('cascade');
    $table->foreignId('promo_id')->nullable()->constrained('promos')->onDelete('set null');
    $table->string('order_code')->unique();
    $table->decimal('subtotal', 10, 2);
    $table->decimal('discount_amount', 10, 2)->default(0);
    $table->decimal('shipping_cost', 10, 2);
    $table->decimal('total_price', 10, 2);
    $table->string('payment_method');
    $table->enum('order_status', ['pending', 'paid', 'processed', 'shipped', 'completed', 'canceled']);
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->index('order_code');
    $table->index('created_at');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
