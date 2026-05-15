<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();

        return $this->successResponse('Kategori berhasil diambil', CategoryResource::collection($categories));
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return $this->successResponse('Kategori berhasil dibuat', new CategoryResource($category), 201);
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);

        return $this->successResponse('Kategori berhasil diambil', new CategoryResource($category));
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->update($request->validated());

        return $this->successResponse('Kategori berhasil diperbarui', new CategoryResource($category));
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();

        return $this->successResponse('Kategori berhasil dihapus');
    }
}
