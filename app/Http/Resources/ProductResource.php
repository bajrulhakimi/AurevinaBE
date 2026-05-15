<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $mainImage = $this->main_image
            ? (Str::startsWith($this->main_image, ['http://', 'https://']) ? $this->main_image : asset('storage/' . $this->main_image))
            : null;
        $ratingAverage = $this->reviews_avg_rating ?? ($this->relationLoaded('reviews') ? $this->reviews->avg('rating') : 0);
        $reviewsCount = $this->reviews_count ?? ($this->relationLoaded('reviews') ? $this->reviews->count() : 0);

        return [
            'id' => $this->id,
            'name' => $this->product_name,
            'product_name' => $this->product_name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'total_stock' => (int) $this->total_stock,
            'base_price' => (float) $this->base_price,
            'special_price' => $this->special_price ? (float) $this->special_price : null,
            'special_start_date' => $this->special_start_date,
            'special_end_date' => $this->special_end_date,
            'has_special_price' => $this->hasActiveSpecialPrice(),
            'final_price' => $this->final_price,
            'weight' => (float) $this->weight,
            'main_image' => $mainImage,
            'status' => $this->status,
            'show_on_hero' => (bool) $this->show_on_hero,
            'hero_position' => $this->hero_position ? (int) $this->hero_position : null,
            'sold_count' => (int) ($this->sold_count ?? 0),
            'rating_average' => round((float) ($ratingAverage ?? 0), 1),
            'reviews_count' => (int) ($reviewsCount ?? 0),
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->category_name,
                'category_name' => $this->category?->category_name,
                'slug' => $this->category?->slug,
            ],
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
