<?php

namespace App\Services;

use App\Models\SellerProfile;
use App\Repositories\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Interfaces\SellerRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

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
            $profile = $this->sellerRepository->create([
                'user_id' => $user->id,
                'shop_name' => $data['shop_name'],
                'description' => $data['description'] ?? null,
                'logo' => $data['logo'] ?? null,
                'status' => 'active',
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

            return $profile;
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

    public function getProfileBySlug(string $slug): SellerProfile
    {
        $profile = $this->sellerRepository->findBySlug($slug);

        if (!$profile) {
            throw new \Exception('Seller profile not found.');
        }

        return $profile->load('user');
    }

    public function updateProfile(string $sellerId, array $data): SellerProfile
    {
        $this->sellerRepository->update($sellerId, $data);

        return $this->sellerRepository->findOrFail($sellerId);
    }

    public function suspend(string $sellerId, ?string $reason = null): SellerProfile
    {
        return $this->sellerRepository->suspend($sellerId, $reason);
    }

    public function unsuspend(string $sellerId): SellerProfile
    {
        return $this->sellerRepository->unsuspend($sellerId);
    }

    public function getRevenue(string $sellerId, string $startDate, string $endDate): array
    {
        return $this->sellerRepository->getRevenue($sellerId, $startDate, $endDate);
    }

    public function getAll(int $perPage = 15)
    {
        return $this->sellerRepository->paginate($perPage);
    }
}
