<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;

class CartService
{
    public function getCart($userId)
    {
        $cart = Cart::firstOrCreate(['user_id' => $userId]);

        return CartItem::with(['product', 'variant'])
            ->where('cart_id', $cart->id)
            ->get();
    }

    public function addToCart($userId, $data)
    {
        $cart = Cart::firstOrCreate(['user_id' => $userId]);
        $product = Product::findOrFail($data['product_id']);
        $variant = null;
        $price = $product->base_price;
        $stock = $product->total_stock;

        if (!empty($data['variant_id'])) {
            $variant = ProductVariant::findOrFail($data['variant_id']);
            if ($variant->product_id !== $product->id) {
                throw new \Exception('Varian tidak valid untuk produk ini');
            }
            $price += $variant->additional_price;
            $stock = $variant->stock;
        }

        if ($stock < $data['quantity']) {
            throw new \Exception('Stok tidak mencukupi untuk produk atau varian yang dipilih');
        }

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->when($variant, fn($query) => $query->where('variant_id', $variant->id))
            ->first();

        if ($item) {
            $item->increment('quantity', $data['quantity']);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $data['quantity'],
                'price' => $price,
            ]);
        }
    }
}
