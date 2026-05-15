<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Product;

class ProductRepository implements ProductRepositoryInterface
{
    public function getAll($request)
    {
        $query = Product::with(['category', 'images', 'variants'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->published();

        if ($request->boolean('hero')) {
            $query
                ->where('show_on_hero', true)
                ->whereNotNull('main_image')
                ->orderByRaw('hero_position IS NULL, hero_position ASC')
                ->latest()
                ->limit(4);
        } elseif ($request->boolean('featured')) {
            $query
                ->withSum('orderItems as sold_count', 'quantity')
                ->orderByRaw('COALESCE(sold_count, 0) DESC')
                ->latest();
        } else {
            $query->latest();
        }

        if ($request->search) {
            $query->where('product_name', 'like', "%{$request->search}%");
        }

        if ($request->category) {
            $query->whereHas('category', fn($categoryQuery) =>
                $categoryQuery->where('slug', $request->category)
            );
        }

        if ($request->boolean('hero')) {
            return $query->get();
        }

        if ($request->filled('per_page')) {
            return $query->paginate((int) $request->input('per_page', 10));
        }

        return $query->get();
    }

    public function find($id)
    {
        return Product::with([
                'category',
                'images',
                'variants',
                'reviews' => fn($query) => $query->with(['user', 'repliedBy'])->latest(),
            ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->findOrFail($id);
    }

    public function getById($id)
    {
        return $this->find($id);
    }

    public function getByCategory($categoryId)
    {
        return Product::with(['category', 'images', 'variants'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->published()
            ->where('category_id', $categoryId)
            ->paginate(10);
    }

    public function search($query)
    {
        return Product::with(['category', 'images', 'variants'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->published()
            ->where('product_name', 'like', "%{$query}%")
            ->paginate(10);
    }

    public function create(array $data)
    {
        return Product::create($data);
    }

    public function update($id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);

        return $product;
    }

    public function delete($id)
    {
        return Product::findOrFail($id)->delete();
    }
}
