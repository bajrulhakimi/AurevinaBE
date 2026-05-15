<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category_name' => $this->category_name,
            'slug' => $this->slug,
            'description' => $this->description ?? null,
            'products_count' => $this->whenCounted('products'),
        ];
    }
}
