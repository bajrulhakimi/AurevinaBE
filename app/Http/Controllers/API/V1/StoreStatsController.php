<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class StoreStatsController extends Controller
{
    public function __invoke()
    {
        return $this->successResponse('Statistik toko berhasil diambil', [
            'products_count' => Product::where('status', 'active')->count(),
            'customers_count' => User::where('role', 'customer')->count(),
            'rating_average' => round((float) Review::avg('rating'), 1),
            'reviews_count' => Review::count(),
        ]);
    }
}
