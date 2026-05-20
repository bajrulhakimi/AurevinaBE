<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'product_name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:products,slug,' . $productId,
            'sku' => 'sometimes|string|max:100|unique:products,sku,' . $productId,
            'description' => 'sometimes|nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'total_stock' => 'sometimes|integer|min:0',
            'base_price' => 'sometimes|numeric|min:0',
            'special_price' => 'sometimes|nullable|numeric|min:0',
            'special_start_date' => 'sometimes|nullable|date',
            'special_end_date' => 'sometimes|nullable|date|after_or_equal:special_start_date',
            'weight' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:draft,active,inactive,archived',
            'show_on_hero' => 'sometimes|nullable|boolean',
            'hero_position' => 'sometimes|nullable|integer|min:1|max:4',
            'image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'existing_images' => 'nullable|array|max:5',
            'existing_images.*' => 'nullable|string|max:2048',
            'variations_enabled' => 'nullable|boolean',
            'color_variations' => 'nullable|array',
            'color_variations.*.color' => 'required_with:color_variations|string|max:100',
            'color_variations.*.sku' => 'required_with:color_variations|string|max:100|distinct',
            'color_variations.*.price' => 'required_with:color_variations|numeric|min:0',
            'color_variations.*.stock' => 'required_with:color_variations|integer|min:0',
            'color_variations.*.image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'color_variations.*.existing_image' => 'nullable|string|max:2048',
            'size_variations' => 'nullable|array',
            'size_variations.*.size' => 'required_with:size_variations|string|max:100',
        ];
    }
}
