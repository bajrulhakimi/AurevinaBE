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
        Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->unique()->constrained('orders')->onDelete('cascade');
    $table->string('payment_method');
    $table->string('payment_proof')->nullable();
    $table->enum('payment_status', ['pending', 'verified', 'rejected']);
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('created_at')->useCurrent();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
