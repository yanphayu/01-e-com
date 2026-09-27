<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\ProductReported;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    private function notifiable(): Authenticatable
    {
        return auth()->user();
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->notifiable()
                ->unreadNotifications()
                ->where('type', ProductReported::class)
                ->count(),
        ]);
    }

    public function markAllAsRead(): RedirectResponse
    {
        $this->notifiable()->unreadNotifications->markAsRead();

        return back();
    }

    public function markAsRead(DatabaseNotification $notification): RedirectResponse
    {
        $user = $this->notifiable();

        if ((int) $notification->notifiable_id !== (int) $user->getAuthIdentifier()) {
            throw new ModelNotFoundException;
        }

        $notification->markAsRead();

        return back();
    }
}
