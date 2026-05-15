<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPaymentRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function upload(UploadPaymentRequest $request, $orderId)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $paymentProof = $request->file('payment_proof')->store('payments', 'public');

        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'payment_method' => $order->payment_method,
                'payment_proof' => $paymentProof,
                'payment_status' => 'pending',
                'paid_at' => now(),
            ]
        );

        return $this->successResponse('Bukti pembayaran berhasil diunggah', [
            'order' => new OrderResource($order->fresh('payment')),
            'payment' => $payment,
        ], 201);
    }
}
