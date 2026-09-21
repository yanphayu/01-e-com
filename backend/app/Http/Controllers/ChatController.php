<?php

namespace App\Http\Controllers;

use App\Events\MessageUpdatedEvent;
use App\Events\NewMessageEvent;
use App\Models\Block;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $archived = $request->boolean('archived');

        $conversations = Conversation::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->whereHas('messages')
            ->where('is_archived', $archived)
            ->with([
                'user1' => fn ($q) => $q->with('profile'),
                'user2' => fn ($q) => $q->with('profile'),
                'lastMessage' => fn ($q) => $q->with('user'),
            ])
            ->withCount(['messages as unread_count' => function ($q) use ($userId) {
                $q->where('user_id', '!=', $userId)->whereNull('read_at');
            }])
            ->orderByDesc('is_pinned')
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

        if ($this->isBlocked($authId, $otherId)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot message this user.',
            ], 403);
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
            ->with([
                'user.profile',
                'product.images',
                'repliedMessage.user.profile',
                'repliedMessage.product.images',
                'reactions.user.profile',
            ])
            ->orderBy('created_at')
            ->paginate(50);

        $messages->getCollection()->transform(
            fn (Message $message) => Message::toPayload($message)
        );

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
            'images' => 'nullable|array|max:10',
            'images.*' => 'nullable|image|max:5120',
            'voice' => 'nullable|file|mimes:mp3,wav,ogg,webm,m4a,oga,opus|max:10240',
            'product_id' => 'nullable|exists:products,id',
            'image_path' => 'nullable|string',
            'replied_to' => 'nullable|exists:messages,id',
            'forwarded' => 'nullable|boolean',
        ]);

        $userId = $request->user()->id;

        if ($conversation->user1_id !== $userId && $conversation->user2_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($request->filled('replied_to')) {
            $replyExists = Message::where('id', $request->replied_to)
                ->where('conversation_id', $conversation->id)
                ->exists();

            if (! $replyExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reply to this message.',
                ], 422);
            }
        }

        $otherId = $conversation->getOtherUser($userId)->id;
        if ($this->isBlocked($userId, $otherId)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot message this user.',
            ], 403);
        }

        $hasBody = ! empty($request->body);
        $hasImage = $request->hasFile('image');
        $hasImages = $request->hasFile('images');
        $hasVoice = $request->hasFile('voice');
        $hasProduct = ! empty($request->product_id);
        $hasForwardedImage = $request->filled('image_path');

        if (! $hasBody && ! $hasImage && ! $hasImages && ! $hasVoice && ! $hasProduct && ! $hasForwardedImage) {
            return response()->json([
                'success' => false,
                'message' => 'Message must have text, image, voice, or product.',
            ], 422);
        }

        $imagePath = null;
        if ($hasImages) {
            $imagePaths = collect($request->file('images', []))
                ->map(fn ($image) => $image->store('chat-images', 'public'))
                ->values()
                ->all();
            $imagePath = $imagePaths[0] ?? null;
        } elseif ($hasImage) {
            $imagePath = $request->file('image')->store('chat-images', 'public');
            $imagePaths = $imagePath ? [$imagePath] : [];
        } elseif ($hasForwardedImage && str_starts_with($request->image_path, 'chat-images/')) {
            $imagePath = $request->image_path;
            $imagePaths = [$imagePath];
        } else {
            $imagePaths = [];
        }

        $voicePath = null;
        if ($hasVoice) {
            $voicePath = $request->file('voice')->store('chat-audio', 'public');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'product_id' => $request->product_id,
            'replied_to' => $request->replied_to,
            'forwarded' => $request->boolean('forwarded'),
            'body' => $request->body,
            'image' => $imagePath,
            'images' => $imagePaths ?? [],
            'voice' => $voicePath,
        ])->load([
            'user.profile',
            'product.images',
            'repliedMessage.user.profile',
            'repliedMessage.product.images',
            'reactions.user.profile',
        ]);

        $conversation->update(['last_message_at' => $message->created_at]);

        try {
            broadcast(new NewMessageEvent($message));
        } catch (\Exception $e) {
            // Broadcast failure should not prevent message from being saved
        }

        return response()->json([
            'success' => true,
            'data' => Message::toPayload($message),
        ]);
    }

    public function updateMessage(Request $request, Message $message): JsonResponse
    {
        if (! $this->isMessageParticipant($request->user(), $message)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($message->deleted_at) {
            return response()->json([
                'success' => false,
                'message' => 'This message has been deleted.',
            ], 422);
        }

        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message->update([
            'body' => $request->body,
            'edited_at' => now(),
        ]);
        $message->refresh();

        $this->broadcastMessageUpdated('edited', $message);

        return response()->json([
            'success' => true,
            'data' => Message::toPayload($message),
        ]);
    }

    public function pinMessage(Request $request, Message $message): JsonResponse
    {
        if (! $this->isMessageParticipant($request->user(), $message)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'pinned' => 'required|boolean',
        ]);

        $message->update(['is_pinned' => $request->boolean('pinned')]);
        $message->refresh();

        $this->broadcastMessageUpdated('pinned', $message);

        return response()->json([
            'success' => true,
            'data' => Message::toPayload($message),
        ]);
    }

    public function destroyMessage(Request $request, Message $message): JsonResponse
    {
        if (! $this->isMessageParticipant($request->user(), $message)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if (! $message->deleted_at) {
            $message->update([
                'body' => null,
                'image' => null,
                'voice' => null,
                'product_id' => null,
                'deleted_at' => now(),
            ]);
            $message->refresh();

            $this->broadcastMessageUpdated('deleted', $message);
        }

        return response()->json([
            'success' => true,
            'data' => Message::toPayload($message),
        ]);
    }

    public function reactToMessage(Request $request, Message $message): JsonResponse
    {
        if (! $this->isMessageParticipant($request->user(), $message)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($message->deleted_at) {
            return response()->json([
                'success' => false,
                'message' => 'This message has been deleted.',
            ], 422);
        }

        $request->validate([
            'reaction' => 'nullable|string|max:32',
        ]);

        $allowedReactions = ['👍', '❤️', '😂', '😮', '😢', '🙏'];
        $reaction = $request->input('reaction');
        $userId = $request->user()->id;

        $existing = MessageReaction::where('message_id', $message->id)
            ->where('user_id', $userId)
            ->first();

        if (! $reaction || ! in_array($reaction, $allowedReactions, true)) {
            $existing?->delete();
        } elseif ($existing && $existing->reaction === $reaction) {
            $existing->delete();
        } elseif ($existing) {
            $existing->update(['reaction' => $reaction]);
        } else {
            MessageReaction::create([
                'message_id' => $message->id,
                'user_id' => $userId,
                'reaction' => $reaction,
            ]);
        }

        $message->load('reactions.user.profile');

        $this->broadcastMessageUpdated('reacted', $message);

        return response()->json([
            'success' => true,
            'data' => Message::toPayload($message),
        ]);
    }

    private function isMessageParticipant(User $user, Message $message): bool
    {
        return Conversation::where('id', $message->conversation_id)
            ->where(function ($query) use ($user) {
                $query->where('user1_id', $user->id)
                    ->orWhere('user2_id', $user->id);
            })
            ->exists();
    }

    private function broadcastMessageUpdated(string $action, Message $message): void
    {
        try {
            broadcast(new MessageUpdatedEvent($action, $message));
        } catch (\Exception $e) {
            // Broadcast failure should not prevent the change from being applied
        }
    }

    public function update(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        if ($conversation->user1_id !== $userId && $conversation->user2_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'is_pinned' => 'nullable|boolean',
            'is_muted' => 'nullable|boolean',
            'is_archived' => 'nullable|boolean',
        ]);

        $conversation->update(array_filter(
            $request->only(['is_pinned', 'is_muted', 'is_archived']),
            fn ($value) => $value !== null
        ));

        return response()->json([
            'success' => true,
            'data' => $conversation,
        ]);
    }

    public function markUnread(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        if ($conversation->user1_id !== $userId && $conversation->user2_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        Message::where('conversation_id', $conversation->id)
            ->where('user_id', '!=', $userId)
            ->whereNotNull('read_at')
            ->update(['read_at' => null]);

        return response()->json([
            'success' => true,
            'data' => ['unread' => true],
        ]);
    }

    public function block(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        if ($conversation->user1_id !== $userId && $conversation->user2_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $blockedId = $conversation->getOtherUser($userId)->id;

        Block::firstOrCreate([
            'blocker_id' => $userId,
            'blocked_id' => $blockedId,
        ]);

        $conversation->update(['is_archived' => true]);

        return response()->json([
            'success' => true,
            'message' => 'User blocked.',
            'data' => $conversation,
        ]);
    }

    public function destroy(Request $request, Conversation $conversation): JsonResponse
    {
        $userId = $request->user()->id;

        if ($conversation->user1_id !== $userId && $conversation->user2_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted.',
        ]);
    }

    public function blockedUsers(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $blocked = Block::where('blocker_id', $userId)
            ->with('blocked.profile')
            ->orderByDesc('updated_at')
            ->get()
            ->pluck('blocked');

        return response()->json([
            'success' => true,
            'data' => $blocked->values(),
        ]);
    }

    public function unblockUser(Request $request, User $user): JsonResponse
    {
        $deleted = Block::where('blocker_id', $request->user()->id)
            ->where('blocked_id', $user->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted ? 'User unblocked.' : 'No block found.',
        ]);
    }

    private function isBlocked(int $authId, int $otherId): bool
    {
        return DB::table('blocks')
            ->where(function ($q) use ($authId, $otherId) {
                $q->where('blocker_id', $authId)->where('blocked_id', $otherId);
            })
            ->orWhere(function ($q) use ($authId, $otherId) {
                $q->where('blocker_id', $otherId)->where('blocked_id', $authId);
            })
            ->exists();
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
