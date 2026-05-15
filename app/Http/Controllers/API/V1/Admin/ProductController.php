<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images', 'variants'])->latest();
        $products = $request->filled('per_page')
            ? $query->paginate((int) $request->input('per_page', 10))
            : $query->get();

        return $this->successResponse('Produk berhasil diambil', ProductResource::collection($products));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        unset($data['image'], $data['images'], $data['color_variations'], $data['size_variations'], $data['variations_enabled']);
        
        $uploadedImages = $request->file('images', []);
        if ($request->hasFile('image') && empty($uploadedImages)) {
            $uploadedImages = [$request->file('image')];
        }

        if (!empty($uploadedImages)) {
            $data['main_image'] = $uploadedImages[0]->store('products', 'public');
        }

        $data['slug'] = !empty($data['slug']) ? $data['slug'] : Str::slug($data['product_name']);
        $data['sku'] = !empty($data['sku']) ? $data['sku'] : Str::upper(Str::slug($data['product_name'])) . '-' . time();
        $data['description'] = $data['description'] ?? '';
        $data['weight'] = $data['weight'] ?? 0;
        $data['base_price'] = $this->resolveBasePrice($data['base_price'] ?? 0, $request->input('color_variations', []));
        $this->prepareHeroFields($data);

        $product = Product::create($data);

        $this->saveProductImages($product, $uploadedImages, $data['main_image'] ?? null);

        // Handle variations
        $this->saveVariations($product, $request);

        return $this->successResponse('Produk berhasil dibuat', new ProductResource($product->load(['category', 'images', 'variants'])), 201);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'images', 'variants'])->findOrFail($id);

        return $this->successResponse('Produk berhasil diambil', new ProductResource($product));
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $data = $request->validated();
        unset($data['image'], $data['images'], $data['color_variations'], $data['size_variations'], $data['variations_enabled']);

        $uploadedImages = $request->file('images', []);
        if ($request->hasFile('image') && empty($uploadedImages)) {
            $uploadedImages = [$request->file('image')];
        }

        if (!empty($uploadedImages)) {
            $data['main_image'] = $uploadedImages[0]->store('products', 'public');
        }

        if (!empty($data['product_name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['product_name']);
        }

        if (array_key_exists('base_price', $data)) {
            $data['base_price'] = $this->resolveBasePrice($data['base_price'] ?? 0, $request->input('color_variations', []));
        }
        $this->prepareHeroFields($data, $product);

        $product->update($data);

        if (!empty($uploadedImages)) {
            $product->images()->delete();
            $this->saveProductImages($product, $uploadedImages, $data['main_image'] ?? null);
        }

        if ($request->has('color_variations') || $request->has('variations_enabled')) {
            $this->saveVariations($product, $request);
        }

        return $this->successResponse('Produk berhasil diperbarui', new ProductResource($product->load(['category', 'images', 'variants'])));
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();

        return $this->successResponse('Produk berhasil dihapus');
    }

    public function updateVariantStock(Request $request, ProductVariant $variant)
    {
        $data = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $variant->update(['stock' => $data['stock']]);
        $variant->product()->update([
            'total_stock' => $variant->product->variants()->sum('stock'),
        ]);

        return $this->successResponse('Stok varian berhasil diperbarui', [
            'variant' => $variant->fresh(),
            'total_stock' => $variant->product->fresh()->total_stock,
        ]);
    }

    /**
     * Save product color variations.
     *
     * Sizes are product information only and should be written in the
     * product description, not stored as separate sellable variants.
     */
    private function saveVariations(Product $product, $request)
    {
        $colorVariations = $request->input('color_variations', []);

        // Delete existing variants if updating
        if ($product->wasRecentlyCreated === false) {
            $product->variants()->delete();
        }

        foreach ($colorVariations as $colorIndex => $colorVar) {
            $colorName = trim($colorVar['color'] ?? '');
            if ($colorName === '' && !$request->hasFile("color_variations.{$colorIndex}.image") && empty($colorVar['existing_image'])) {
                continue;
            }

            $colorName = $colorName !== '' ? $colorName : 'Varian ' . ($colorIndex + 1);
            $sku = trim($colorVar['sku'] ?? '');
            $variantData = [
                'color' => $colorName,
                'size' => 'All Size',
                'sku' => $sku !== '' ? $sku : "{$product->sku}-" . Str::upper(Str::slug($colorName)),
                'stock' => (int) ($colorVar['stock'] ?? 0),
                'additional_price' => max(0, ((float) ($colorVar['price'] ?? $product->base_price)) - (float) $product->base_price),
            ];

            if ($request->hasFile("color_variations.{$colorIndex}.image")) {
                $variantData['variant_image'] = $request->file("color_variations.{$colorIndex}.image")->store('products/variants', 'public');
            } elseif (!empty($colorVar['existing_image'])) {
                $variantData['variant_image'] = $colorVar['existing_image'];
            }

            $product->variants()->create($variantData);
        }

        if ($product->variants()->exists()) {
            $product->update(['total_stock' => $product->variants()->sum('stock')]);
        }
    }

    private function saveProductImages(Product $product, array $uploadedImages, ?string $mainImage): void
    {
        if ($mainImage) {
            $product->images()->create(['image_url' => $mainImage]);
        }

        foreach (array_slice($uploadedImages, 1, 4) as $image) {
            $product->images()->create([
                'image_url' => $image->store('products', 'public'),
            ]);
        }
    }

    private function resolveBasePrice($basePrice, array $colorVariations): float
    {
        $basePrice = (float) $basePrice;
        if ($basePrice > 0 || empty($colorVariations)) {
            return $basePrice;
        }

        $variantPrices = collect($colorVariations)
            ->pluck('price')
            ->filter(fn($price) => is_numeric($price) && (float) $price > 0)
            ->map(fn($price) => (float) $price);

        return $variantPrices->min() ?? $basePrice;
    }

    private function prepareHeroFields(array &$data, ?Product $product = null): void
    {
        if (!array_key_exists('show_on_hero', $data)) {
            return;
        }

        $showOnHero = filter_var($data['show_on_hero'], FILTER_VALIDATE_BOOLEAN);
        $data['show_on_hero'] = $showOnHero;

        if (!$showOnHero) {
            $data['hero_position'] = null;
            return;
        }

        $heroQuery = Product::where('show_on_hero', true);
        if ($product) {
            $heroQuery->whereKeyNot($product->id);
        }

        if ($heroQuery->count() >= 4 && (!$product || !$product->show_on_hero)) {
            abort(422, 'Maksimal 4 produk yang bisa tampil di hero.');
        }

        if (empty($data['hero_position'])) {
            $usedPositions = $heroQuery
                ->whereNotNull('hero_position')
                ->pluck('hero_position')
                ->map(fn($position) => (int) $position)
                ->all();

            $data['hero_position'] = collect(range(1, 4))
                ->first(fn($position) => !in_array($position, $usedPositions, true)) ?? 4;
        }
    }
}
