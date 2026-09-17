<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ProductReported extends Notification
{
    use Queueable;

    public function __construct(
        public Report $report,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->report->id,
            'type' => 'product_reported',
            'user' => [
                'id' => $this->report->user_id,
                'name' => $this->report->user->name,
                'avatar' => $this->report->user->profile?->avatar,
            ],
            'product_id' => $this->report->product_id,
            'product_name' => $this->report->product->name,
            'reason' => $this->report->reason,
            'details' => $this->report->details,
            'created_at' => $this->report->created_at->toISOString(),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
