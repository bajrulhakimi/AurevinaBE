<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\UserAddress;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
    }

    public function checkout(CheckoutRequest $request)
    {
        try {
            if ($request->user()->isCustomer() && !$request->user()->hasVerifiedEmail()) {
                return $this->errorResponse('Verifikasi email terlebih dahulu sebelum checkout.', [], 403);
            }

            $data = $request->validated();

            if (empty($data['address_id'])) {
                $shipping = $data['shipping'];
                if (!empty($shipping['is_default'])) {
                    $request->user()->addresses()->update(['is_default' => false]);
                }

                $address = UserAddress::create([
                    'user_id' => $request->user()->id,
                    'receiver_name' => $shipping['full_name'],
                    'receiver_phone' => $shipping['phone'],
                    'province' => '-',
                    'city' => $shipping['city'],
                    'district' => '-',
                    'postal_code' => $shipping['postal_code'],
                    'full_address' => $shipping['address'],
                    'is_default' => (bool) ($shipping['is_default'] ?? false),
                ]);

                $data['address_id'] = $address->id;
            } else {
                $addressBelongsToUser = $request->user()
                    ->addresses()
                    ->where('id', $data['address_id'])
                    ->exists();

                if (!$addressBelongsToUser) {
                    return $this->errorResponse('Alamat tidak sesuai dengan akun pelanggan.', [], 422);
                }
            }

            unset($data['shipping']);

            $order = $this->orderService->checkout($request->user(), $data);

            return $this->successResponse('Checkout berhasil. Pesanan masuk setelah pembayaran diterima.', new OrderResource($order->load(['user', 'address', 'items', 'payment', 'shipping'])), 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product', 'items.variant', 'payment', 'shipping'])
            ->latest()
            ->paginate(10);

        return $this->successResponse('Daftar pesanan berhasil diambil', OrderResource::collection($orders));
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['items.product', 'items.variant', 'payment', 'shipping'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return $this->successResponse('Detail pesanan berhasil diambil', new OrderResource($order));
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($id);

        if (!in_array($order->order_status, ['pending', 'confirmed'], true)) {
            return $this->errorResponse('Pesanan tidak bisa dibatalkan karena sudah diproses/dikirim.', [], 422);
        }

        $order->update(['order_status' => 'cancelled']);

        return $this->successResponse('Pesanan berhasil dibatalkan', new OrderResource($order->fresh(['items.product', 'items.variant', 'payment', 'shipping'])));
    }

    public function complete(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($id);

        if ($order->order_status !== 'shipped') {
            return $this->errorResponse('Pesanan hanya bisa diselesaikan setelah status dikirim.', [], 422);
        }

        $order->update(['order_status' => 'delivered']);
        $order->shipping()->updateOrCreate(
            ['order_id' => $order->id],
            ['shipping_status' => 'delivered', 'delivered_at' => now()]
        );

        return $this->successResponse('Pesanan berhasil ditandai selesai', new OrderResource($order->fresh(['items.product', 'items.variant', 'payment', 'shipping'])));
    }
}
