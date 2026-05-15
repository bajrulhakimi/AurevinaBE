<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductImageResource extends JsonResource
{
    public function toArray($request)
    {
        $imageUrl = $this->image_url
            ? (Str::startsWith($this->image_url, ['http://', 'https://']) ? $this->image_url : asset('storage/' . $this->image_url))
            : null;

        return [
            'id' => $this->id,
            'image_url' => $imageUrl,
        ];
    }
}
