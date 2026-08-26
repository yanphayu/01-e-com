<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PurgeUnverifiedUsers extends Command
{
    protected $signature = 'auth:purge-unverified';

    protected $description = 'Delete users who have not verified their email after the verification code expired';

    public function handle(): int
    {
        $deleted = User::whereNull('email_verified_at')
            ->whereNotNull('email_verification_expires_at')
            ->where('email_verification_expires_at', '<', now())
            ->delete();

        $this->info("Deleted {$deleted} unverified user(s).");

        return self::SUCCESS;
    }
}
