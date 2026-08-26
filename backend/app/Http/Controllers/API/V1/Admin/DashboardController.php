<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_sellers' => User::where('role', 'seller')->count(),
                'total_couriers' => User::where('role', 'courier')->count(),
                'total_products' => Product::count(),
                'total_orders' => Order::count(),
                'total_revenue' => (float) Order::where('status', 'completed')->sum('total_amount'),
            ];
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function recentOrders(): JsonResponse
    {
        try {
            $orders = Order::with('buyer', 'seller', 'items.product')
                ->latest()
                ->limit(20)
                ->get();
            return response()->json($orders);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function topSellers(): JsonResponse
    {
        try {
            $sellers = User::where('role', 'seller')
                ->withSum('sellerOrders', 'total_amount')
                ->orderByDesc('seller_orders_sum_total_amount')
                ->limit(10)
                ->get();
            return response()->json($sellers);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function topProducts(): JsonResponse
    {
        try {
            $products = Product::withSum('orderItems', 'quantity')
                ->orderByDesc('order_items_sum_quantity')
                ->limit(10)
                ->get();
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
