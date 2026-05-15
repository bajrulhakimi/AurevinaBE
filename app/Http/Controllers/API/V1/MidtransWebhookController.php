<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans)
    {
        $payload = $request->all();

        if (!$midtrans->verifySignature($payload)) {
            return $this->errorResponse('Signature Midtrans tidak valid.', [], 403);
        }

        $order = Order::with('payment')->where('order_code', $payload['order_id'] ?? null)->firstOrFail();
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        $paymentStatus = match (true) {
            in_array($transactionStatus, ['capture', 'settlement'], true) && $fraudStatus !== 'challenge' => 'verified',
            in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'], true) => 'rejected',
            default => 'pending',
        };

        $order->payment?->update([
            'payment_status' => $paymentStatus,
            'gateway_transaction_id' => $payload['transaction_id'] ?? $order->payment?->gateway_transaction_id,
            'gateway_payload' => $payload,
            'paid_at' => $paymentStatus === 'verified' ? now() : $order->payment?->paid_at,
        ]);

        if ($paymentStatus === 'verified' && $order->order_status === 'pending') {
            $order->update(['order_status' => 'confirmed']);
        }

        if ($paymentStatus === 'rejected' && $order->order_status === 'pending') {
            $order->update(['order_status' => 'cancelled']);
        }

        return $this->successResponse('Notifikasi Midtrans berhasil diproses.');
    }
}
