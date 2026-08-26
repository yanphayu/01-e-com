<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByEmailWithRoles(string $email)
    {
        return $this->model->where('email', $email)->with('roles')->first();
    }

    public function attachRole(string $userId, string $roleId)
    {
        $user = $this->findOrFail($userId);
        $user->roles()->attach($roleId);

        return $user->fresh(['roles']);
    }

    public function syncRoles(string $userId, array $roleIds)
    {
        $user = $this->findOrFail($userId);
        $user->roles()->sync($roleIds);

        return $user->fresh(['roles']);
    }

    public function hasRole(string $userId, string $roleName): bool
    {
        $user = $this->findOrFail($userId);

        return $user->roles->contains('name', $roleName);
    }

    public function suspend(string $userId, ?string $reason = null)
    {
        $user = $this->findOrFail($userId);
        $user->update([
            'is_suspended' => true,
            'suspension_reason' => $reason,
            'suspended_at' => now(),
        ]);

        return $user->fresh();
    }

    public function unsuspend(string $userId)
    {
        $user = $this->findOrFail($userId);
        $user->update([
            'is_suspended' => false,
            'suspension_reason' => null,
            'suspended_at' => null,
        ]);

        return $user->fresh();
    }

    public function getDashboardStats(): array
    {
        $totalUsers = $this->model->count();
        $activeUsers = $this->model->where('is_suspended', false)->count();
        $suspendedUsers = $this->model->where('is_suspended', true)->count();

        $usersByRole = DB::table('users')
            ->join('user_role', 'users.id', '=', 'user_role.user_id')
            ->join('roles', 'user_role.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as count'))
            ->groupBy('roles.name')
            ->pluck('count', 'name')
            ->toArray();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'suspended_users' => $suspendedUsers,
            'users_by_role' => $usersByRole,
        ];
    }
}