<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
    ) {}

    public function getProfile(User $user): User
    {
        return $user->load('roles');
    }

    public function updateProfile(User $user, array $data): User
    {
        $this->userRepository->update($user->id, $data);

        return $user->fresh()->load('roles');
    }

    public function getAll(array $filters, int $perPage = 15)
    {
        return $this->userRepository->search($filters, $perPage);
    }

    public function getById(string $id): User
    {
        return $this->userRepository->findOrFail($id);
    }

    public function suspend(string $userId, ?string $reason = null): User
    {
        return $this->userRepository->suspend($userId, $reason);
    }

    public function unsuspend(string $userId): User
    {
        return $this->userRepository->unsuspend($userId);
    }

    public function getDashboardStats(): array
    {
        return $this->userRepository->getDashboardStats();
    }
}
