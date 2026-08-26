<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage_users',
            'manage_sellers',
            'manage_couriers',
            'manage_products',
            'manage_categories',
            'manage_orders',
            'manage_payments',
            'manage_reviews',
            'manage_coupons',
            'manage_reports',
            'view_dashboard',
            'sell_products',
            'buy_products',
            'deliver_orders',
            'manage_own_store',
            'manage_own_products',
            'manage_own_orders',
        ];

        $permissionModels = collect();

        foreach ($permissions as $permission) {
            $permissionModels->push(Permission::create([
                'id' => (string) Str::uuid(),
                'name' => $permission,
                'slug' => $permission,
            ]));
        }

        $roles = [
            'admin' => $permissionModels->pluck('slug')->toArray(),
            'seller' => ['sell_products', 'manage_own_store', 'manage_own_products', 'manage_own_orders', 'view_dashboard'],
            'buyer' => ['buy_products'],
            'courier' => ['deliver_orders', 'view_dashboard'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::create([
                'id' => (string) Str::uuid(),
                'name' => $roleName,
                'slug' => $roleName,
            ]);

            $role->permissions()->attach(
                $permissionModels->filter(fn ($p) => in_array($p->slug, $rolePermissions))->pluck('id')
            );
        }
    }
}
