<?php

namespace App\Interfaces;

interface ProductRepositoryInterface
{
    public function getAll($request);
    public function getById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getByCategory($categoryId);
    public function search($query);
}