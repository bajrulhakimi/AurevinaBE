<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'address', 'items.product', 'payment', 'shipping'])->latest();

        match ($request->query('period')) {
            'today' => $query->whereDate('created_at', today()),
            '7d' => $query->where('created_at', '>=', now()->subDays(7)),
            '30d' => $query->where('created_at', '>=', now()->subDays(30)),
            default => null,
        };

        $orders = $query->paginate(15);

        return $this->successResponse('Pesanan berhasil diambil', OrderResource::collection($orders));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'address', 'items.product', 'payment', 'shipping'])->findOrFail($id);

        return $this->successResponse('Detail pesanan berhasil diambil', new OrderResource($order));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'order_status' => 'sometimes|required|in:pending,confirmed,processed,shipped,delivered,cancelled',
            'payment_status' => 'sometimes|required|in:pending,verified,rejected',
            'courier' => 'sometimes|nullable|string|max:100',
            'tracking_number' => 'sometimes|nullable|string|max:100',
        ]);

        $order = Order::with(['payment', 'shipping'])->findOrFail($id);

        if ($request->filled('order_status')) {
            $order->update(['order_status' => $request->order_status]);
        }

        if ($request->filled('payment_status') && $order->payment) {
            $order->payment->update(['payment_status' => $request->payment_status]);
        }

        if ($request->hasAny(['courier', 'tracking_number']) || $request->order_status === 'shipped') {
            $shippingStatus = match ($request->order_status) {
                'processed' => 'packed',
                'shipped' => 'shipped',
                'delivered' => 'delivered',
                default => $order->shipping?->shipping_status ?? 'waiting',
            };

            $order->shipping()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'courier' => $request->input('courier', $order->shipping?->courier),
                    'tracking_number' => $request->input('tracking_number', $order->shipping?->tracking_number),
                    'shipping_status' => $shippingStatus,
                    'shipped_at' => $shippingStatus === 'shipped' ? ($order->shipping?->shipped_at ?? now()) : $order->shipping?->shipped_at,
                    'delivered_at' => $shippingStatus === 'delivered' ? ($order->shipping?->delivered_at ?? now()) : $order->shipping?->delivered_at,
                ]
            );
        }

        return $this->successResponse('Status pesanan berhasil diperbarui', new OrderResource($order->fresh(['user', 'address', 'items.product', 'payment', 'shipping'])));
    }

    public function bulkShip(Request $request)
    {
        $data = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
            'courier' => 'required|string|max:100',
            'tracking_numbers' => 'nullable|array',
            'tracking_numbers.*' => 'nullable|string|max:100',
        ]);

        $orders = Order::whereIn('id', $data['order_ids'])->get();

        foreach ($orders as $order) {
            $trackingNumber = $data['tracking_numbers'][$order->id] ?? null;

            $order->update(['order_status' => 'shipped']);
            $order->shipping()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'courier' => $data['courier'],
                    'tracking_number' => $trackingNumber,
                    'shipping_status' => 'shipped',
                    'shipped_at' => now(),
                ]
            );
        }

        return $this->successResponse('Pengiriman masal berhasil diproses');
    }

    public function destroy($id)
    {
        Order::findOrFail($id)->delete();

        return $this->successResponse('Pesanan berhasil dihapus');
    }
}
