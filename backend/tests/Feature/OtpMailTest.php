<?php

namespace Tests\Feature;

use App\Mail\SendAccountDelete;
use App\Mail\SendEmailVerify;
use App\Mail\SendPasswordReset;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OtpMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_mails_a_zero_padded_six_digit_code(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'Sokha',
            'email' => 'sokha@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $otp = Otp::where('type', Otp::TYPE_EMAIL_VERIFY)->sole();

        $this->assertSame(6, strlen($otp->otp));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp->otp);
        $this->assertTrue($otp->expires_at->between(now()->addMinutes(9), now()->addMinutes(11)));

        Mail::assertSent(SendEmailVerify::class, function (SendEmailVerify $mail) use ($otp): bool {
            $html = $mail->render();

            return $mail->otp === $otp->otp
                && $mail->expiresInMinutes === 10
                && str_contains($html, $otp->otp)
                && str_contains($html, '10 minutes');
        });
    }

    public function test_verification_consumes_the_code_and_returns_a_token(): void
    {
        Mail::fake();

        $user = $this->makeUnverifiedUser();
        $otp = Otp::issueFor($user, Otp::TYPE_EMAIL_VERIFY);

        $response = $this->postJson('/api/verify', [
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $otp->otp,
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing('otps', ['id' => $otp->id]);
        $this->assertNotNull($response->json('token'));
    }

    public function test_a_verification_code_cannot_be_replayed(): void
    {
        Mail::fake();

        $user = $this->makeUnverifiedUser();
        $otp = Otp::issueFor($user, Otp::TYPE_EMAIL_VERIFY);

        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $otp->otp,
        ];

        $this->postJson('/api/verify', $payload)->assertOk();

        $this->postJson('/api/verify', $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Invalid OTP.');
    }

    public function test_a_code_issued_for_another_purpose_cannot_verify_an_email(): void
    {
        Mail::fake();

        $user = $this->makeUnverifiedUser();
        $resetOtp = Otp::issueFor($user, Otp::TYPE_PASSWORD_RESET);

        $this->postJson('/api/verify', [
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $resetOtp->otp,
        ])->assertStatus(422)->assertJsonPath('message', 'Invalid OTP.');

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_an_expired_verification_code_is_rejected_and_removed(): void
    {
        Mail::fake();

        $user = $this->makeUnverifiedUser();
        $otp = Otp::issueFor($user, Otp::TYPE_EMAIL_VERIFY);

        $this->travel(11)->minutes();

        $this->postJson('/api/verify', [
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $otp->otp,
        ])->assertStatus(422)->assertJsonPath('message', 'OTP has expired.');

        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing('otps', ['id' => $otp->id]);
    }

    public function test_resending_invalidates_the_previously_mailed_code(): void
    {
        Mail::fake();

        $user = $this->makeUnverifiedUser();
        $first = Otp::issueFor($user, Otp::TYPE_EMAIL_VERIFY);

        $this->postJson('/api/resend', [
            'user_id' => $user->id,
            'email' => $user->email,
        ])->assertOk()->assertJsonPath('success', true);

        $second = Otp::where('type', Otp::TYPE_EMAIL_VERIFY)->sole();

        $this->assertNotSame($first->otp, $second->otp);
        $this->assertDatabaseMissing('otps', ['id' => $first->id]);

        $this->postJson('/api/verify', [
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $first->otp,
        ])->assertStatus(422);

        $this->postJson('/api/verify', [
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $second->otp,
        ])->assertOk();
    }

    public function test_forgot_password_hides_whether_the_email_exists(): void
    {
        Mail::fake();

        $this->postJson('/api/forgot-password', ['email' => 'nobody@example.com'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'If the email exists, a verification code has been sent.');

        Mail::assertNothingSent();
        $this->assertDatabaseCount('otps', 0);
    }

    public function test_forgot_password_mails_a_code_and_replaces_earlier_reset_codes(): void
    {
        Mail::fake();

        $user = $this->makeUser();
        $stale = Otp::issueFor($user, Otp::TYPE_PASSWORD_RESET);

        $this->postJson('/api/forgot-password', ['email' => $user->email])
            ->assertOk()
            ->assertJsonPath('success', true);

        $fresh = Otp::where('type', Otp::TYPE_PASSWORD_RESET)->sole();

        $this->assertDatabaseMissing('otps', ['id' => $stale->id]);
        $this->assertSame(15, $fresh->minutesUntilExpiry());

        Mail::assertSent(SendPasswordReset::class, fn (SendPasswordReset $mail): bool => $mail->otp === $fresh->otp
            && $mail->expiresInMinutes === 15
            && str_contains($mail->render(), '15 minutes'));
    }

    public function test_reset_password_consumes_the_code_and_revokes_tokens(): void
    {
        Mail::fake();

        $user = $this->makeUser();
        $token = $user->createToken('login_token')->plainTextToken;
        $otp = Otp::issueFor($user, Otp::TYPE_PASSWORD_RESET);

        $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'otp' => $otp->otp,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseCount('otps', 0);
    }

    public function test_account_deletion_code_cannot_be_used_to_verify_an_email(): void
    {
        Mail::fake();

        $user = $this->makeUnverifiedUser();
        Sanctum::actingAs($user);

        $this->postJson('/api/profile/delete-otp')->assertOk();

        $otp = Otp::where('type', Otp::TYPE_ACCOUNT_DELETE)->sole();

        Mail::assertSent(SendAccountDelete::class, fn (SendAccountDelete $mail): bool => $mail->otp === $otp->otp
            && $mail->expiresInMinutes === 5
            && str_contains($mail->render(), '5 minutes'));

        $this->postJson('/api/verify', [
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $otp->otp,
        ])->assertStatus(422);
    }

    public function test_mailing_codes_is_rate_limited_per_email(): void
    {
        Mail::fake();

        $user = $this->makeUnverifiedUser();
        $payload = ['user_id' => $user->id, 'email' => $user->email];

        foreach (range(1, 3) as $attempt) {
            $this->postJson('/api/resend', $payload)->assertOk();
        }

        $this->postJson('/api/resend', $payload)->assertStatus(429);

        Mail::assertSentCount(3);
        $this->assertSame(1, Otp::where('type', Otp::TYPE_EMAIL_VERIFY)->count());
    }

    public function test_submitting_codes_is_rate_limited(): void
    {
        Mail::fake();

        $user = $this->makeUnverifiedUser();
        $payload = ['user_id' => $user->id, 'email' => $user->email, 'otp' => '000000'];

        foreach (range(1, 10) as $attempt) {
            $this->postJson('/api/verify', $payload)->assertStatus(422);
        }

        $this->postJson('/api/verify', $payload)->assertStatus(429);
    }

    public function test_logging_in_with_an_unverified_account_keeps_the_already_mailed_code(): void
    {
        Mail::fake();

        $user = $this->makeUnverifiedUser();
        $otp = Otp::issueFor($user, Otp::TYPE_EMAIL_VERIFY);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('verify_required', true)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('expires_in', 600);

        Mail::assertNothingSent();

        $this->assertDatabaseHas('otps', ['id' => $otp->id, 'otp' => $otp->otp]);
    }

    public function test_logging_in_with_an_unverified_account_reports_no_expiry_when_no_code_is_pending(): void
    {
        Mail::fake();

        $user = $this->makeUnverifiedUser();

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(403)
            ->assertJsonPath('verify_required', true)
            ->assertJsonPath('expires_in', null);

        Mail::assertNothingSent();
    }

    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ], $overrides));
    }

    private function makeUnverifiedUser(): User
    {
        return $this->makeUser(['email_verified_at' => null]);
    }
}
