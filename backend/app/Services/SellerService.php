<?php

namespace App\Services;

use App\Models\SellerProfile;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\SellerRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellerService
{
    public function __construct(
        protected SellerRepositoryInterface $sellerRepository,
        protected UserRepositoryInterface $userRepository,
        protected NotificationRepositoryInterface $notificationRepository,
    ) {}

    public function createProfile(\App\Models\User $user, array $data): SellerProfile
    {
        return DB::transaction(function () use ($user, $data) {
            $storeSlug = Str::slug($data['store_name']);

            $profile = $this->sellerRepository->create([
                'user_id' => $user->id,
                'store_name' => $data['store_name'],
                'store_slug' => $storeSlug,
                'description' => $data['description'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'logo' => $data['logo'] ?? null,
                'banner' => $data['banner'] ?? null,
                'is_suspended' => false,
            ]);

            $sellerRole = \App\Models\Role::where('slug', 'seller')->first();
            if ($sellerRole) {
                $this->userRepository->attachRole($user->id, $sellerRole->id);
            }

            $this->notificationRepository->createNotification(
                $user->id,
                'Seller Profile Created',
                'Your seller profile has been created successfully.',
                'seller_profile',
            );

            return $profile->load('user');
        });
    }

    public function getProfile(string $userId): SellerProfile
    {
        $profile = $this->sellerRepository->findByUserId($userId);

        if (!$profile) {
            throw new \Exception('Seller profile not found.');
        }

        return $profile->load('user');
    }

    public function getProfileById(string $id): SellerProfile
    {
        $profile = $this->sellerRepository->find($id);

        if (!$profile) {
            throw new \Exception('Seller profile not found.');
        }

        return $profile->load('user', 'products');
    }

    public function getProfileBySlug(string $slug): SellerProfile
    {
        $profile = $this->sellerRepository->findBySlug($slug);

        if (!$profile) {
            throw new \Exception('Seller profile not found.');
        }

        return $profile->load('user', 'products');
    }

    public function updateProfile(string $sellerId, array $data): SellerProfile
    {
        if (isset($data['store_name']) && !isset($data['store_slug'])) {
            $data['store_slug'] = Str::slug($data['store_name']);
        }

        $this->sellerRepository->update($sellerId, $data);

        return $this->sellerRepository->findOrFail($sellerId)->load('user');
    }

    public function suspend(string $sellerId, ?string $reason = null): SellerProfile
    {
        return $this->sellerRepository->suspend($sellerId, $reason);
    }

    public function unsuspend(string $sellerId): SellerProfile
    {
        return $this->sellerRepository->unsuspend($sellerId);
    }

    public function getRevenue(string $sellerId, string $startDate, string $endDate): float
    {
        return (float) $this->sellerRepository->getRevenue($sellerId, $startDate, $endDate);
    }

    public function getAll(int $perPage = 15)
    {
        return $this->sellerRepository->paginate($perPage);
    }
}
