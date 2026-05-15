<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        return $this->successResponse('Kode promo hanya dikirim melalui email pelanggan.', []);
    }

    public function validateCode(Request $request)
    {
        $data = $request->validate([
            'promo_code' => 'required|string|max:100',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $promo = Promo::where('promo_code', strtoupper(trim($data['promo_code'])))
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if (!$promo) {
            return $this->errorResponse('Kode promo tidak ditemukan atau sudah tidak aktif.', [], 404);
        }

        $subtotal = (float) $data['subtotal'];
        $discountAmount = match ($promo->discount_type) {
            'percent' => round($subtotal * ((float) $promo->discount_value / 100), 2),
            default => (float) $promo->discount_value,
        };

        $discountAmount = min($discountAmount, $subtotal);

        return $this->successResponse('Kode promo berhasil digunakan.', [
            'discount_type' => $promo->discount_type,
            'discount_value' => (float) $promo->discount_value,
            'discount_amount' => $discountAmount,
        ]);
    }
}
