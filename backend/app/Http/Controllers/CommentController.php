<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use App\Notifications\CommentCreated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request, Product $product): JsonResponse
    {
        $comments = Comment::with([
            'user.profile',
            'replies' => function ($q) {
                $q->latest();
            },
            'replies.user.profile',
            'replies.replies' => function ($q) {
                $q->latest();
            },
            'replies.replies.user.profile',
            'replies.replies.replies' => function ($q) {
                $q->latest();
            },
            'replies.replies.replies.user.profile',
        ])
            ->where('product_id', $product->id)
            ->whereNull('parent_id')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $request->user()->comments()->create([
            'product_id' => $product->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'body' => $validated['body'],
        ]);

        $comment->load('user.profile', 'product');

        if ($product->user_id !== $request->user()->id) {
            try {
                $product->user->notify(new CommentCreated($comment));
            } catch (\Exception $e) {
                // Broadcast may fail if Reverb is not running — notification is still saved to DB
            }
        }

        return response()->json([
            'success' => true,
            'data' => $comment,
        ], 201);
    }

    public function destroy(Request $request, Product $product, Comment $comment): JsonResponse
    {
        if ($request->user()->id !== $comment->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted',
        ]);
    }
}
