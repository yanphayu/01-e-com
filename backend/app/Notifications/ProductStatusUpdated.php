<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProductStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public Product $product,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->product->id,
            'type' => 'product_status_updated',
            'status' => $this->product->status,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'created_at' => $this->product->updated_at->toISOString(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->product->id,
            'type' => 'product_status_updated',
            'status' => $this->product->status,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'created_at' => $this->product->updated_at->toISOString(),
        ]);
    }
}
