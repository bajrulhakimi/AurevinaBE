<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $totalBlogs = Blog::count();
        $totalRevenue = Order::sum('total_price');

        $monthlyRevenue = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_price');

        $pendingOrders = Order::where('order_status', 'pending')->count();
        $shippedOrders = Order::where('order_status', 'shipped')->count();
        $deliveredOrders = Order::where('order_status', 'delivered')->count();

        return $this->successResponse('Dashboard statistika', [
            'users' => $totalUsers,
            'products' => $totalProducts,
            'categories' => $totalCategories,
            'orders' => $totalOrders,
            'blogs' => $totalBlogs,
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'pending_orders' => $pendingOrders,
            'shipped_orders' => $shippedOrders,
            'delivered_orders' => $deliveredOrders,
        ]);
    }
}
