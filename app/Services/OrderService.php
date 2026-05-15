<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Promo;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function checkout($user, array $data)
    {
        $cart = Cart::with(['items.product', 'items.variant'])
            ->where('user_id', $user->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            throw new \Exception('Keranjang kosong');
        }

        DB::beginTransaction();

        try {
            $subtotal = 0;
            foreach ($cart->items as $item) {
                $product = $item->product;
                $variant = $item->variant;
                $price = $product->final_price + ($variant?->additional_price ?? 0);
                $stock = $variant ? $variant->stock : $product->total_stock;

                if ($stock < $item->quantity) {
                    throw new \Exception("Stok {$product->product_name} tidak cukup");
                }

                $subtotal += $price * $item->quantity;
            }

            $promo = null;
            $discountAmount = 0;

            if (!empty($data['promo_code'])) {
                $promo = Promo::where('promo_code', $data['promo_code'])
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->first();

                if (!$promo) {
                    throw new \Exception('Kode promo tidak valid atau tidak aktif');
                }

                $discountAmount = match ($promo->discount_type) {
                    'percent' => round($subtotal * ($promo->discount_value / 100), 2),
                    default => $promo->discount_value,
                };

                $discountAmount = min($discountAmount, $subtotal);
            }

            $shippingCost = ($data['shipping_method'] ?? 'regular') === 'express' ? 50000 : 25000;
            $totalPrice = $subtotal - $discountAmount + $shippingCost;

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $data['address_id'],
                'promo_id' => $promo?->id,
                'order_code' => 'INV-' . time(),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'shipping_cost' => $shippingCost,
                'total_price' => $totalPrice,
                'payment_method' => $data['payment_method'],
                'order_status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            $paymentProof = null;
            if (!empty($data['payment_proof'])) {
                $paymentProof = $data['payment_proof']->store('payments', 'public');
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $data['payment_method'],
                'payment_gateway' => $data['payment_method'] === 'midtrans' ? 'midtrans' : null,
                'payment_proof' => $paymentProof,
                'payment_status' => 'pending',
                'paid_at' => $paymentProof ? now() : null,
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;
                $variant = $item->variant;
                $price = $product->final_price + ($variant?->additional_price ?? 0);
                $variantName = $variant ? trim("{$variant->color} {$variant->size}") : null;
                $image = $variant?->variant_image ?? $product->main_image;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'product_name' => $product->product_name,
                    'variant_name' => $variantName,
                    'product_image' => $image,
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'subtotal' => $price * $item->quantity,
                ]);

                if ($variant) {
                    $variant->decrement('stock', $item->quantity);
                    $product->update(['total_stock' => $product->variants()->sum('stock')]);
                } else {
                    $product->decrement('total_stock', $item->quantity);
                }
            }

            $cart->items()->delete();

            DB::commit();

            if ($data['payment_method'] === 'midtrans') {
                $snap = app(MidtransService::class)->createSnapTransaction($order->fresh(['user', 'address']));
                $payment->update([
                    'gateway_order_id' => $order->order_code,
                    'gateway_payment_url' => $snap['redirect_url'] ?? null,
                    'gateway_payload' => $snap,
                ]);
            }

            // Kirim pesan WhatsApp
            app(WhatsAppService::class)->sendCheckoutMessage($order);

            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
