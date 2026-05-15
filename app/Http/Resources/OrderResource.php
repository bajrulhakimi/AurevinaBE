<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $payment = $this->whenLoaded('payment');
        $paymentData = null;

        if ($this->relationLoaded('payment') && $payment) {
            $proof = $payment->payment_proof;
            $paymentData = [
                'id' => $payment->id,
                'payment_method' => $payment->payment_method,
                'payment_gateway' => $payment->payment_gateway,
                'payment_status' => $payment->payment_status,
                'gateway_payment_url' => $payment->gateway_payment_url,
                'payment_proof' => $proof
                    ? (Str::startsWith($proof, ['http://', 'https://']) ? $proof : asset('storage/' . $proof))
                    : null,
                'paid_at' => $payment->paid_at,
            ];
        }

        $shipping = $this->whenLoaded('shipping');
        $shippingData = null;

        if ($this->relationLoaded('shipping') && $shipping) {
            $shippingData = [
                'id' => $shipping->id,
                'courier' => $shipping->courier,
                'tracking_number' => $shipping->tracking_number,
                'shipping_status' => $shipping->shipping_status,
                'shipped_at' => $shipping->shipped_at,
                'delivered_at' => $shipping->delivered_at,
            ];
        }

        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'user_id' => $this->user_id,
            'address_id' => $this->address_id,
            'promo_id' => $this->promo_id,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'shipping_cost' => $this->shipping_cost,
            'total_price' => $this->total_price,
            'payment_method' => $this->payment_method,
            'order_status' => $this->order_status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'address' => $this->whenLoaded('address'),
            'payment' => $paymentData,
            'shipping' => $shippingData,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
