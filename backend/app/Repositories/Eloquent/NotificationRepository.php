<?php

namespace App\Repositories\Eloquent;

use App\Models\Notification;
use App\Repositories\Interfaces\NotificationRepositoryInterface;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    public function getForUser(string $userId, int $perPage = 20, ?string $type = null)
    {
        $query = $this->model->where('user_id', $userId);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->latest()->paginate($perPage);
    }

    public function markAsRead(string $notificationId)
    {
        $notification = $this->findOrFail($notificationId);
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $notification->fresh();
    }

    public function markAllAsRead(string $userId)
    {
        $this->model
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return true;
    }

    public function getUnreadCount(string $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    public function createNotification(string $userId, string $title, string $message, string $type, array $data = [])
    {
        return $this->model->create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data,
        ]);
    }
}