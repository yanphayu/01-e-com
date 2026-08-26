<?php

namespace Database\Seeders;

use App\Models\Courier;
use App\Models\Role;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
        $admin->roles()->attach(Role::where('slug', 'admin')->first());

        $seller = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Seller User',
            'email' => 'seller@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
        $seller->roles()->attach(Role::where('slug', 'seller')->first());
        SellerProfile::create([
            'id' => (string) Str::uuid(),
            'user_id' => $seller->id,
            'store_name' => 'Test Store',
            'store_slug' => 'test-store',
        ]);

        $buyer = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Buyer User',
            'email' => 'buyer@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
        $buyer->roles()->attach(Role::where('slug', 'buyer')->first());

        $courier = User::create([
            'id' => (string) Str::uuid(),
            'name' => 'Courier User',
            'email' => 'courier@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);
        $courier->roles()->attach(Role::where('slug', 'courier')->first());
        Courier::create([
            'id' => (string) Str::uuid(),
            'user_id' => $courier->id,
            'vehicle_type' => 'motorcycle',
            'phone' => $courier->phone ?? '012345678',
        ]);
    }
}
