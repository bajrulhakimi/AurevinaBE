<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'order_item_id' => 'nullable|exists:order_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:2000',
        ];
    }
}
