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
        Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
    $table->string('product_name');
    $table->string('slug')->unique();
    $table->string('sku')->unique();
    $table->text('description');
    $table->integer('total_stock');
    $table->decimal('base_price', 10, 2);
    $table->decimal('weight', 8, 2);
    $table->string('main_image')->nullable();
    $table->enum('status', ['draft', 'active', 'inactive', 'archived']);
    $table->timestamps();
    $table->softDeletes();
    $table->index('product_name');
    $table->index('slug');
    $table->index('sku');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
