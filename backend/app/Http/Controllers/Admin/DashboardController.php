<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalCategories = Category::count();
        $totalComments = Comment::count();
        $pendingReports = Report::where('status', 'pending')->count();
        $recentUsers = User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']);
        $recentReports = Report::with(['product' => fn ($q) => $q->select('id', 'name'), 'user:id,name'])
            ->latest()
            ->take(5)
            ->get(['id', 'user_id', 'product_id', 'reason', 'status', 'created_at']);

        $totalConversations = Conversation::count();
        $totalMessages = Message::count();
        $todayMessages = Message::where('created_at', '>=', now()->startOfDay())->count();

        $messagesGrouped = Message::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at'])
            ->groupBy(fn ($m) => $m->created_at->toDateString())
            ->map->count();

        $messagesByDay = collect(range(6, 0))->map(function (int $i) use ($messagesGrouped) {
            $day = now()->subDays($i)->toDateString();

            return [
                'date' => $day,
                'count' => (int) ($messagesGrouped[$day] ?? 0),
            ];
        })->values();

        $topConversations = Message::query()
            ->select('conversation_id')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('conversation_id')
            ->with('conversation.user1:id,name', 'conversation.user2:id,name')
            ->groupBy('conversation_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $messagesByConversation = $topConversations->map(function (Message $m) {
            $conversation = $m->conversation;

            return [
                'id' => $m->conversation_id,
                'label' => $conversation
                    ? ($conversation->user1?->name ?: 'User').' & '.($conversation->user2?->name ?: 'User')
                    : "Conversation #{$m->conversation_id}",
                'count' => (int) $m->total,
            ];
        })->values();

        $otherMessages = Message::whereNotNull('conversation_id')
            ->whereNotIn('conversation_id', $topConversations->pluck('conversation_id'))
            ->count();

        if ($otherMessages > 0) {
            $messagesByConversation->push([
                'id' => null,
                'label' => 'Other',
                'count' => $otherMessages,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_users' => $totalUsers,
                    'total_categories' => $totalCategories,
                    'total_comments' => $totalComments,
                    'pending_reports' => $pendingReports,
                ],
                'recent_users' => $recentUsers,
                'recent_reports' => $recentReports,
                'chat' => [
                    'total_conversations' => $totalConversations,
                    'total_messages' => $totalMessages,
                    'today_messages' => $todayMessages,
                    'messages_by_day' => $messagesByDay,
                    'messages_by_conversation' => $messagesByConversation,
                ],
            ],
        ]);
    }
}
