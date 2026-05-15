<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class MidtransService
{
    public function createSnapTransaction(Order $order): array
    {
        $serverKey = config('services.midtrans.server_key');

        if (!$serverKey) {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY belum diatur di file .env.');
        }

        $baseUrl = config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_code,
                'gross_amount' => (int) round($order->total_price),
            ],
            'customer_details' => [
                'first_name' => $order->user?->name,
                'email' => $order->user?->email,
                'phone' => $order->address?->receiver_phone,
                'shipping_address' => [
                    'first_name' => $order->address?->receiver_name,
                    'phone' => $order->address?->receiver_phone,
                    'city' => $order->address?->city,
                    'postal_code' => $order->address?->postal_code,
                    'address' => $order->address?->full_address,
                ],
            ],
            'callbacks' => [
                'finish' => config('app.frontend_url', env('FRONTEND_URL', 'http://127.0.0.1:5173')) . '/orders',
            ],
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post($baseUrl . '/snap/v1/transactions', $payload);

        if ($response->failed()) {
            throw new \RuntimeException($response->json('error_messages.0') ?? 'Gagal membuat transaksi Midtrans.');
        }

        return $response->json();
    }

    public function verifySignature(array $payload): bool
    {
        $serverKey = config('services.midtrans.server_key');

        if (!$serverKey || empty($payload['signature_key'])) {
            return false;
        }

        $rawSignature = ($payload['order_id'] ?? '')
            . ($payload['status_code'] ?? '')
            . ($payload['gross_amount'] ?? '')
            . $serverKey;

        return hash('sha512', $rawSignature) === $payload['signature_key'];
    }
}
