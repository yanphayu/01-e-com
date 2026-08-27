<?php

namespace App\Models;

use App\Notifications\ResetPasswordApi;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasApiTokens, Notifiable, SoftDeletes, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'avatar',
        'facebook',
        'instagram',
        'twitter',
        'is_active',
        'is_suspended',
        'suspended_at',
        'suspension_reason',
        'email_verification_code',
        'email_verification_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordApi($token));
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function sellerProfile(): HasOne
    {
        return $this->hasOne(SellerProfile::class);
    }

    public function courier(): HasOne
    {
        return $this->hasOne(Courier::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isSeller(): bool
    {
        return $this->hasRole('seller');
    }

    public function isBuyer(): bool
    {
        return $this->hasRole('buyer');
    }

    public function isCourier(): bool
    {
        return $this->hasRole('courier');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
