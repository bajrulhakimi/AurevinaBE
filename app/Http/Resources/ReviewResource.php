<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'order_item_id' => $this->order_item_id,
            'rating' => (int) $this->rating,
            'review' => $this->review,
            'admin_reply' => $this->admin_reply,
            'replied_at' => $this->replied_at,
            'created_at' => $this->created_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'product' => new ProductResource($this->whenLoaded('product')),
            'replied_by_user' => new UserResource($this->whenLoaded('repliedBy')),
        ];
    }
}
