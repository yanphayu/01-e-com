<?php

namespace App\Repositories\Interfaces;

interface NotificationRepositoryInterface extends BaseRepositoryInterface
{
    public function getForUser(string $userId, int $perPage = 20, ?string $type = null);
    public function markAsRead(string $notificationId);
    public function markAllAsRead(string $userId);
    public function getUnreadCount(string $userId): int;
    public function createNotification(string $userId, string $title, string $message, string $type, array $data = []);
}
