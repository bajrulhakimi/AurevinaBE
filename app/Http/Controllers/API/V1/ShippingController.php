<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipping;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function update(Request $request, $orderId)
    {
        $request->validate([
            'courier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
            'shipping_status' => 'required|in:pending,processed,shipped,delivered',
        ]);

        $order = Order::findOrFail($orderId);

        $shipping = Shipping::updateOrCreate(
            ['order_id' => $order->id],
            [
                'courier' => $request->courier,
                'tracking_number' => $request->tracking_number,
                'shipping_status' => $request->shipping_status,
                'shipped_at' => $request->shipping_status === 'shipped' ? now() : null,
                'delivered_at' => $request->shipping_status === 'delivered' ? now() : null,
            ]
        );

        return $this->successResponse('Status pengiriman berhasil diperbarui', $shipping);
    }
}
