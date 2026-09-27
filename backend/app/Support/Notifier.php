<?php

namespace App\Support;

use App\Events\NotificationCreated;
use App\Models\User;
use Illuminate\Notifications\Notification;

class Notifier
{
    /**
     * Store the notification for the user and broadcast it immediately.
     *
     * Laravel's own broadcast channel queues its event, so the notification
     * would only reach the browser once a queue worker runs. Dispatching our
     * own ShouldBroadcastNow event keeps real-time delivery in sync with the
     * chat events, which are broadcast immediately.
     */
    public static function send(User $user, Notification $notification): void
    {
        $user->notify($notification);

        NotificationCreated::dispatch(
            $user->id,
            method_exists($notification, 'toArray')
                ? $notification->toArray($user)
                : [],
        );
    }
}
