<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Otp extends Model
{
    public const TYPE_EMAIL_VERIFY = 'email_verify';

    public const TYPE_PASSWORD_RESET = 'password_reset';

    public const TYPE_ACCOUNT_DELETE = 'account_delete';

    protected $fillable = [
        'user_id',
        'type',
        'otp',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Issue a fresh code for the given purpose, replacing any earlier code of
     * the same type so only the most recently mailed code can ever be used.
     */
    public static function issueFor(User|int $user, string $type): self
    {
        $userId = $user instanceof User ? $user->getKey() : $user;

        self::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->delete();

        return self::query()->create([
            'user_id' => $userId,
            'type' => $type,
            'otp' => self::generateCode(),
            'expires_at' => now()->addMinutes(self::ttlFor($type)),
        ]);
    }

    /**
     * Zero padded so a code always keeps the length the API validates against.
     */
    public static function generateCode(): string
    {
        $length = (int) config('otp.length', 6);
        $max = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    public static function ttlFor(string $type): int
    {
        return (int) config("otp.ttl.{$type}", config('otp.ttl.email_verify', 10));
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function minutesUntilExpiry(): int
    {
        return max(1, (int) ceil(now()->diffInSeconds($this->expires_at) / 60));
    }
}
