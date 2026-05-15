<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'total_stock' => 'required|integer|min:0',
            'base_price' => 'required|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0|lt:base_price',
            'special_start_date' => 'nullable|date',
            'special_end_date' => 'nullable|date|after_or_equal:special_start_date',
            'weight' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'show_on_hero' => 'nullable|boolean',
            'hero_position' => 'nullable|integer|min:1|max:4',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
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
