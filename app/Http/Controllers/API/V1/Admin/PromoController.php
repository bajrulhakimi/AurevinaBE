<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromoResource;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        return $this->successResponse('Promo berhasil diambil', PromoResource::collection(Promo::latest()->get()));
    }

    public function store(Request $request)
    {
        $data = $this->validatePromo($request);
        $promo = Promo::create($data);

        return $this->successResponse('Promo berhasil dibuat', new PromoResource($promo), 201);
    }

    public function update(Request $request, Promo $promo)
    {
        $promo->update($this->validatePromo($request, $promo->id));

        return $this->successResponse('Promo berhasil diperbarui', new PromoResource($promo));
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();

        return $this->successResponse('Promo berhasil dihapus');
    }

    private function validatePromo(Request $request, ?int $promoId = null): array
    {
        return $request->validate([
            'promo_code' => 'required|string|max:100|unique:promos,promo_code,' . $promoId,
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
    }
}
