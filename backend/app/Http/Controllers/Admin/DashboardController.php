<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalCategories = Category::count();
        $totalComments = Comment::count();
        $recentUsers = User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_users' => $totalUsers,
                    'total_categories' => $totalCategories,
                    'total_comments' => $totalComments,
                ],
                'recent_users' => $recentUsers,
            ],
        ]);
    }
}
