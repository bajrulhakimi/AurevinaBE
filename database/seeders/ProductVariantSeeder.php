<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            [
                'name' => 'Black',
                'code' => 'BLK',
                'image' => 'https://images.unsplash.com/photo-1591195853828-11db59a44f6b?w=900&h=900&fit=crop',
            ],
            [
                'name' => 'Ivory',
                'code' => 'IVR',
                'image' => 'https://images.unsplash.com/photo-1617330890776-eecdc0a28a35?w=900&h=900&fit=crop',
            ],
            [
                'name' => 'Dusty Pink',
                'code' => 'PNK',
                'image' => 'https://images.unsplash.com/photo-1596215898707-a51b2e5fa1d0?w=900&h=900&fit=crop',
            ],
            [
                'name' => 'Sage Green',
                'code' => 'SGE',
                'image' => 'https://images.unsplash.com/photo-1596215898637-7b0b7a6b1f5e?w=900&h=900&fit=crop',
            ],
        ];

        Product::query()->with('variants')->each(function (Product $product) use ($colors) {
            foreach ($colors as $index => $color) {
                $skuBase = $product->sku ?: Str::upper(Str::slug($product->product_name));
                $stock = max(5, (int) floor($product->total_stock / count($colors)));
                $additionalPrice = $index === 0 ? 0 : $index * 5000;

                $product->variants()->updateOrCreate(
                    ['sku' => "{$skuBase}-{$color['code']}"],
                    [
                        'color' => $color['name'],
                        'size' => 'All Size',
                        'stock' => $stock,
                        'additional_price' => $additionalPrice,
                        'variant_image' => $color['image'],
                    ]
                );
            }

            if (!$product->main_image) {
                $product->update(['main_image' => $colors[0]['image']]);
            }
        });
    }
}
