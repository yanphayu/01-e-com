<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CommentCreated extends Notification
{
    use Queueable;

    public function __construct(
        public Comment $comment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->comment->id,
            'type' => 'comment_created',
            'user' => [
                'id' => $this->comment->user_id,
                'name' => $this->comment->user->name,
                'avatar' => $this->comment->user->profile?->avatar,
            ],
            'product_id' => $this->comment->product_id,
            'product_name' => $this->comment->product->name,
            'body' => $this->comment->body,
            'created_at' => $this->comment->created_at->toISOString(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->comment->id,
            'type' => 'comment_created',
            'user' => [
                'id' => $this->comment->user_id,
                'name' => $this->comment->user->name,
                'avatar' => $this->comment->user->profile?->avatar,
            ],
            'product_id' => $this->comment->product_id,
            'product_name' => $this->comment->product->name,
            'body' => $this->comment->body,
            'created_at' => $this->comment->created_at->toISOString(),
        ]);
    }
}
