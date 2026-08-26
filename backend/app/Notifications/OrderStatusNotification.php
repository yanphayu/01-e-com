<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $orderNumber,
        public string $status,
        public ?string $message = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Order #{$this->orderNumber} - Status Updated")
            ->line("Your order #{$this->orderNumber} status has been updated to: {$this->status}")
            ->line($this->message ?? '');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_number' => $this->orderNumber,
            'status' => $this->status,
            'message' => $this->message,
        ];
    }
}
