<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    public function sendCheckoutMessage($order)
    {
        $message = "🛍 *Konfirmasi Pesanan*\n\n" .
            "Halo {$order->user->name},\n\n" .
            "Pesanan Anda dengan kode *{$order->order_code}* telah berhasil dibuat.\n\n" .
            "📋 *Detail Pesanan:*\n" .
            "Total: Rp " . number_format($order->total_price, 0, ',', '.') . "\n" .
            "Metode Pembayaran: {$order->payment_method}\n\n" .
            "Silakan upload bukti pembayaran di aplikasi.\n\n" .
            "Terima kasih telah berbelanja di Aurevina!";

        // Implementasi menggunakan Twilio atau API WhatsApp lainnya
        // Contoh menggunakan HTTP request ke API WhatsApp
        $response = Http::post('https://api.whatsapp.com/send', [
            'phone' => $order->user->phone,
            'message' => $message,
        ]);

        return $response->successful();
    }
}
