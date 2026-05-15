<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount([
                'products' => fn ($query) => $query->where('status', 'active'),
            ])
            ->orderByDesc('products_count')
            ->orderBy('category_name')
            ->get();

        return $this->successResponse('Kategori berhasil diambil', CategoryResource::collection($categories));
    }
}
