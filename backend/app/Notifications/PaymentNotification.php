<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $orderNumber,
        public string $status,
        public string $amount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment for Order #{$this->orderNumber} - {$this->status}")
            ->line("Your payment for order #{$this->orderNumber} has been {$this->status}.")
            ->line("Amount: {$this->amount}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_number' => $this->orderNumber,
            'status' => $this->status,
            'amount' => $this->amount,
        ];
    }
}
