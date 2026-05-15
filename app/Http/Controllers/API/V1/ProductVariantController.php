<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductVariantResource;
use App\Models\ProductVariant;

class ProductVariantController extends Controller
{
    public function show($id)
    {
        $variant = ProductVariant::with('product')->findOrFail($id);

        return $this->successResponse('Varian produk berhasil diambil', new ProductVariantResource($variant));
    }
}
