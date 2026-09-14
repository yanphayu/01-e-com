<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $conversations = Conversation::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->with([
                'user1' => fn ($q) => $q->with('profile'),
                'user2' => fn ($q) => $q->with('profile'),
                'lastMessage' => fn ($q) => $q->with('user'),
            ])
            ->withCount(['messages as unread_count' => function ($q) use ($userId) {
                $q->where('user_id', '!=', $userId)->whereNull('read_at');
            }])
            ->orderByDesc('last_message_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $authId = $request->user()->id;
        $otherId = $request->input('user_id');

        if ($authId === $otherId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot start a conversation with yourself.',
            ], 422);
        }

        [$user1, $user2] = $authId < $otherId ? [$authId, $otherId] : [$otherId, $authId];

        $conversation = Conversation::where('user1_id', $user1)
            ->where('user2_id', $user2)
            ->with([
                'user1' => fn ($q) => $q->with('profile'),
                'user2' => fn ($q) => $q->with('profile'),
            ])
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'user1_id' => $user1,
                'user2_id' => $user2,
            ])->load([
                'user1' => fn ($q) => $q->with('profile'),
                'user2' => fn ($q) => $q->with('profile'),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $conversation,
        ]);
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        if ($conversation->user1_id !== $userId && $conversation->user2_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Mark unread messages from the other user as read
        Message::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::where('conversation_id', $conversation->id)
            ->with(['user', 'product.images'])
            ->orderBy('created_at')
            ->paginate(50);

        $messages->getCollection()->transform(function ($message) {
            if ($message->product) {
                $message->product->image = $message->product->images->first()?->image;
            }

            return $message;
        });

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate([
            'body' => 'nullable|string|max:5000',
            'image' => 'nullable|image|max:5120',
            'product_id' => 'nullable|exists:products,id',
        ]);

        $userId = $request->user()->id;

        if ($conversation->user1_id !== $userId && $conversation->user2_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $hasBody = ! empty($request->body);
        $hasImage = $request->hasFile('image');
        $hasProduct = ! empty($request->product_id);

        if (! $hasBody && ! $hasImage && ! $hasProduct) {
            return response()->json([
                'success' => false,
                'message' => 'Message must have text, image, or product.',
            ], 422);
        }

        $imagePath = null;
        if ($hasImage) {
            $imagePath = $request->file('image')->store('chat-images', 'public');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'product_id' => $request->product_id,
            'body' => $request->body,
            'image' => $imagePath,
        ])->load(['user', 'product.images']);

        $conversation->update(['last_message_at' => $message->created_at]);

        try {
            broadcast(new NewMessageEvent($message));
        } catch (\Exception $e) {
            // Broadcast failure should not prevent message from being saved
        }

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $userId = $request->user()->id;

        $users = User::where('id', '!=', $userId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->with('profile')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }
}
