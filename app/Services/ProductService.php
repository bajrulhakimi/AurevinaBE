<?php

namespace App\Services;

use App\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Str;

class ProductService
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAll($request)
    {
        return $this->productRepository->getAll($request);
    }

    public function getById($id)
    {
        return $this->productRepository->getById($id);
    }

    public function create(array $data)
    {
        if (isset($data['image'])) {
            $data['main_image'] = $data['image']->store('products', 'public');
        }

        $data['slug'] = Str::slug($data['product_name']);

        return $this->productRepository->create($data);
    }

    public function update($id, array $data)
    {
        if (isset($data['image'])) {
            $data['main_image'] = $data['image']->store('products', 'public');
        }

        if (isset($data['product_name'])) {
            $data['slug'] = Str::slug($data['product_name']);
        }

        return $this->productRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->productRepository->delete($id);
    }
}
