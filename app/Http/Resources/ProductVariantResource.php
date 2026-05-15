<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductVariantResource extends JsonResource
{
    public function toArray($request)
    {
        $variantType = $this->color ? 'color' : 'size';
        $variantValue = $this->color ?: $this->size;
        $variantImage = $this->variant_image
            ? (Str::startsWith($this->variant_image, ['http://', 'https://']) ? $this->variant_image : asset('storage/' . $this->variant_image))
            : null;

        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'color' => $this->color,
            'size' => $this->size,
            'variant_type' => $variantType,
            'variant_value' => $variantValue,
            'stock' => (int) $this->stock,
            'additional_price' => (float) $this->additional_price,
            'image' => $variantImage,
            'variant_image' => $variantImage,
        ];
    }
}
