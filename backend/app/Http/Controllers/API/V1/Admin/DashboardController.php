<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        try {
            $totalSellers = DB::table('users')
                ->join('user_role', 'users.id', '=', 'user_role.user_id')
                ->join('roles', 'user_role.role_id', '=', 'roles.id')
                ->where('roles.slug', 'seller')
                ->count();

            $totalCouriers = DB::table('users')
                ->join('user_role', 'users.id', '=', 'user_role.user_id')
                ->join('roles', 'user_role.role_id', '=', 'roles.id')
                ->where('roles.slug', 'courier')
                ->count();

            $totalBuyers = DB::table('users')
                ->join('user_role', 'users.id', '=', 'user_role.user_id')
                ->join('roles', 'user_role.role_id', '=', 'roles.id')
                ->where('roles.slug', 'buyer')
                ->count();

            $stats = [
                'total_users' => User::count(),
                'total_sellers' => $totalSellers,
                'total_couriers' => $totalCouriers,
                'total_buyers' => $totalBuyers,
                'total_products' => Product::count(),
                'active_products' => Product::where('is_active', true)->where('is_suspended', false)->count(),
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'delivered_orders' => Order::where('status', 'delivered')->count(),
                'total_revenue' => (float) Order::where('status', 'delivered')->sum('total'),
                'total_users_suspended' => User::where('is_suspended', true)->count(),
            ];
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function recentOrders(): JsonResponse
    {
        try {
            $orders = Order::with('buyer', 'seller.user', 'items.product')
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
            $sellers = \App\Models\SellerProfile::with('user')
                ->where('is_suspended', false)
                ->withCount(['orders as total_orders' => function ($query) {
                    $query->where('status', 'delivered');
                }])
                ->withSum(['orders as total_revenue' => function ($query) {
                    $query->where('status', 'delivered');
                }])
                ->orderByDesc('total_revenue')
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
                ->where('is_active', true)
                ->limit(10)
                ->get();
            return response()->json($products);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
