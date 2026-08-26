<?php

namespace App\Repositories\Interfaces;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email);
    public function findByEmailWithRoles(string $email);
    public function attachRole(string $userId, string $roleId);
    public function syncRoles(string $userId, array $roleIds);
    public function hasRole(string $userId, string $roleName): bool;
    public function suspend(string $userId, ?string $reason = null);
    public function unsuspend(string $userId);
    public function getDashboardStats(): array;
}
