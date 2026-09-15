<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'product_id',
        'replied_to',
        'is_pinned',
        'forwarded',
        'body',
        'image',
        'read_at',
        'edited_at',
        'deleted_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'edited_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_pinned' => 'boolean',
        'forwarded' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function repliedMessage(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replied_to');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    public static function toPayload(self $message, bool $includeReply = true): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'user_id' => $message->user_id,
            'body' => $message->body,
            'image' => $message->image,
            'product_id' => $message->product_id,
            'product' => $message->product ? [
                'id' => $message->product->id,
                'name' => $message->product->name,
                'price' => $message->product->price,
                'image' => $message->product->images->first()?->image,
            ] : null,
            'replied_to' => $message->replied_to,
            'replied_message' => $includeReply && $message->repliedMessage
                ? static::toPayload($message->repliedMessage, false)
                : null,
            'is_pinned' => $message->is_pinned,
            'forwarded' => $message->forwarded,
            'edited_at' => $message->edited_at?->toISOString(),
            'deleted_at' => $message->deleted_at?->toISOString(),
            'created_at' => $message->created_at->toISOString(),
            'read_at' => $message->read_at?->toISOString(),
            'user' => $message->user ? [
                'id' => $message->user->id,
                'name' => $message->user->name,
                'avatar' => $message->user->profile?->avatar,
            ] : null,
            'reactions' => $message->reactions->map(fn ($reaction) => [
                'id' => $reaction->id,
                'user_id' => $reaction->user_id,
                'reaction' => $reaction->reaction,
                'user' => [
                    'name' => $reaction->user?->name,
                    'avatar' => $reaction->user?->profile?->avatar,
                ],
            ])->values(),
        ];
    }
}
