<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:import-folders {path=../Produk}', function (string $path) {
    $sourceRoot = realpath(base_path($path));

    if (!$sourceRoot || !is_dir($sourceRoot)) {
        $this->error("Folder produk tidak ditemukan: {$path}");
        return self::FAILURE;
    }

    $publicRoot = storage_path('app/public');
    $targetRoot = $publicRoot . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . 'imported';
    File::ensureDirectoryExists($targetRoot);
    File::cleanDirectory($targetRoot);

    $productFolders = collect(File::directories($sourceRoot))->sort()->values();
    $imported = 0;
    $variantTotal = 0;

    foreach ($productFolders as $index => $productFolder) {
        $detailPath = $productFolder . DIRECTORY_SEPARATOR . 'Detail.txt';
        $mainImageFolder = $productFolder . DIRECTORY_SEPARATOR . 'Gambar Utama';
        $variantFolder = $productFolder . DIRECTORY_SEPARATOR . 'gambar varian warna';

        if (!File::exists($detailPath)) {
            $this->warn('Lewati, Detail.txt tidak ada: ' . basename($productFolder));
            continue;
        }

        $detail = parseProductDetail(File::get($detailPath), basename($productFolder));
        $category = Category::withTrashed()->firstOrCreate(
            ['slug' => Str::slug($detail['category'])],
            ['category_name' => $detail['category']]
        );

        if ($category->trashed()) {
            $category->restore();
        }

        $slug = Str::slug($detail['name']);
        $sku = 'IMP-' . strtoupper(substr(Str::slug($detail['name'], ''), 0, 28));
        $productTarget = $targetRoot . DIRECTORY_SEPARATOR . $slug;
        if (File::isDirectory($productTarget)) {
            File::deleteDirectory($productTarget);
        }
        File::ensureDirectoryExists($productTarget . DIRECTORY_SEPARATOR . 'main');
        File::ensureDirectoryExists($productTarget . DIRECTORY_SEPARATOR . 'variants');

        $variantFiles = collect(File::exists($variantFolder) ? File::files($variantFolder) : [])
            ->filter(fn ($file) => isImageFile($file->getFilename()))
            ->sortBy(fn ($file) => strtolower($file->getFilename()))
            ->values();

        $stockPerColor = $detail['stock'] ?: 999;
        $totalStock = $variantFiles->count() * $stockPerColor;

        $product = Product::withTrashed()->updateOrCreate(
            ['sku' => $sku],
            [
                'category_id' => $category->id,
                'product_name' => $detail['name'],
                'slug' => $slug,
                'sku' => $sku,
                'description' => $detail['description'],
                'total_stock' => $totalStock,
                'base_price' => $detail['price'],
                'weight' => $detail['weight'],
                'status' => 'active',
                'show_on_hero' => $index < 4,
                'hero_position' => $index < 4 ? $index + 1 : null,
            ]
        );

        if ($product->trashed()) {
            $product->restore();
        }

        $product->images()->delete();
        $product->variants()->delete();

        $mainImages = collect(File::exists($mainImageFolder) ? File::files($mainImageFolder) : [])
            ->filter(fn ($file) => isImageFile($file->getFilename()))
            ->sortBy(fn ($file) => naturalFileSortKey($file->getFilename()))
            ->take(5)
            ->values();

        foreach ($mainImages as $mainIndex => $file) {
            $relativePath = copyProductImage($file->getPathname(), $productTarget . DIRECTORY_SEPARATOR . 'main', 'main-' . ($mainIndex + 1));
            ProductImage::create([
                'product_id' => $product->id,
                'image_url' => $relativePath,
            ]);

            if ($mainIndex === 0) {
                $product->main_image = $relativePath;
                $product->save();
            }
        }

        if (!$product->main_image && $variantFiles->isNotEmpty()) {
            $firstVariantImage = copyProductImage($variantFiles->first()->getPathname(), $productTarget . DIRECTORY_SEPARATOR . 'variants', 'main-fallback');
            $product->main_image = $firstVariantImage;
            $product->save();
        }

        foreach ($variantFiles as $variantIndex => $file) {
            $color = Str::of(pathinfo($file->getFilename(), PATHINFO_FILENAME))
                ->replace(['_', '-'], ' ')
                ->squish()
                ->title()
                ->toString();
            $variantSku = $sku . '-' . strtoupper(substr(Str::slug($color, ''), 0, 22));
            $relativePath = copyProductImage($file->getPathname(), $productTarget . DIRECTORY_SEPARATOR . 'variants', Str::slug($color) ?: 'warna-' . ($variantIndex + 1));

            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => makeUniqueVariantSku($variantSku, $product->id, $variantIndex + 1),
                'color' => $color,
                'size' => 'All Size',
                'stock' => $stockPerColor,
                'additional_price' => 0,
                'variant_image' => $relativePath,
            ]);
        }

        $imported++;
        $variantTotal += $variantFiles->count();
        $this->info("Imported {$detail['name']} ({$variantFiles->count()} varian)");
    }

    $this->info("Selesai: {$imported} produk, {$variantTotal} varian warna.");
    return self::SUCCESS;
})->purpose('Import product folders with detail, main images, and color variants');

if (! function_exists('parseProductDetail')) {
function parseProductDetail(string $text, string $fallbackName): array
{
    $normalized = str_replace(["\r\n", "\r"], "\n", $text);

    return [
        'name' => cleanProductName(extractDetailSection($normalized, 'Nama Produk') ?: $fallbackName),
        'category' => extractDetailSection($normalized, 'Kategori') ?: inferCategoryFromName($fallbackName),
        'description' => extractDetailSection($normalized, 'Deskripsi Produk') ?: $fallbackName,
        'price' => extractDetailNumber($normalized, 'Harga') ?: 0,
        'stock' => extractDetailNumber($normalized, 'Stok') ?: 999,
        'weight' => extractDetailNumber($normalized, 'Berat Produk') ?: 120,
    ];
}

function cleanProductName(string $name): string
{
    return collect(preg_split('/\n+/', $name))
        ->map(fn ($line) => trim($line))
        ->filter()
        ->reject(fn ($line) => preg_match('/^\d+\s*\/\s*\d+$/', $line))
        ->implode(' ');
}

function extractDetailSection(string $text, string $label): string
{
    $pattern = '/\*?\s*' . preg_quote($label, '/') . '\s*\n(?<value>.*?)(?=\n\s*\*\s*\n|\z)/isu';
    if (!preg_match($pattern, $text, $match)) {
        return '';
    }

    return trim($match['value']);
}

function extractDetailNumber(string $text, string $label): int
{
    $pattern = '/' . preg_quote($label, '/') . '\s*:?\s*(?<value>[0-9][0-9.,]*)/iu';
    if (!preg_match($pattern, $text, $match)) {
        return 0;
    }

    return (int) preg_replace('/[^0-9]/', '', $match['value']);
}

function inferCategoryFromName(string $name): string
{
    $lower = Str::lower($name);
    if (Str::contains($lower, 'anak')) {
        return 'Pakaian Muslim Anak Perempuan';
    }

    if (Str::contains($lower, 'bergo')) {
        return 'Bergo';
    }

    return 'Pashmina';
}

function isImageFile(string $filename): bool
{
    return in_array(Str::lower(pathinfo($filename, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
}

function naturalFileSortKey(string $filename): string
{
    $base = pathinfo($filename, PATHINFO_FILENAME);
    return ctype_digit($base) ? str_pad($base, 6, '0', STR_PAD_LEFT) : Str::lower($filename);
}

function copyProductImage(string $source, string $targetFolder, string $baseName): string
{
    File::ensureDirectoryExists($targetFolder);
    $extension = Str::lower(pathinfo($source, PATHINFO_EXTENSION));
    $filename = Str::slug($baseName) . '.' . $extension;
    $target = $targetFolder . DIRECTORY_SEPARATOR . $filename;

    $counter = 2;
    while (File::exists($target)) {
        $filename = Str::slug($baseName) . '-' . $counter . '.' . $extension;
        $target = $targetFolder . DIRECTORY_SEPARATOR . $filename;
        $counter++;
    }

    File::copy($source, $target);

    return Str::of($target)
        ->after(storage_path('app/public') . DIRECTORY_SEPARATOR)
        ->replace(DIRECTORY_SEPARATOR, '/')
        ->toString();
}

function makeUniqueVariantSku(string $baseSku, int $productId, int $index): string
{
    $sku = substr($baseSku, 0, 60);
    if (!ProductVariant::where('sku', $sku)->exists()) {
        return $sku;
    }

    return substr($baseSku, 0, 50) . '-' . $productId . '-' . $index;
}
}
