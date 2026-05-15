<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService)
    {
    }

    public function index(Request $request)
    {
        $products = $this->productService->getAll($request);
        return $this->successResponse('Produk berhasil diambil', ProductResource::collection($products));
    }

    public function show($id)
    {
        $product = $this->productService->getById($id);
        return $this->successResponse('Produk berhasil diambil', new ProductResource($product));
    }
}
