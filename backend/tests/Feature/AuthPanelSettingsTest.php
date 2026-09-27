<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPanelSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_endpoint_returns_the_default_auth_panel_content(): void
    {
        $this->getJson('/api/auth-panel')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.headline_1', 'A calmer way')
            ->assertJsonPath('data.bullet_3', 'Free returns within 30 days');
    }

    public function test_admin_can_update_the_auth_panel_content_and_the_api_reflects_it(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.settings.auth-panel.update'), [
                'headline_1' => 'Shop calmly',
                'headline_2' => 'every day.',
                'sub' => 'A short description of the storefront.',
                'bullet_1' => 'Handpicked sellers',
                'bullet_2' => 'Buyer protection',
                'bullet_3' => 'Support on weekdays',
                'footer' => '© {year} TRINITY Marketplace.',
            ])
            ->assertRedirect(route('admin.settings.auth-panel'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('settings', ['key' => 'headline_1', 'value' => 'Shop calmly']);

        $this->getJson('/api/auth-panel')
            ->assertOk()
            ->assertJsonPath('data.headline_1', 'Shop calmly')
            ->assertJsonPath('data.sub', 'A short description of the storefront.')
            ->assertJsonPath('data.footer', '© {year} TRINITY Marketplace.');
    }

    public function test_empty_fields_fall_back_to_defaults_and_can_be_reset(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $admin = User::factory()->create(['is_admin' => true]);

        Setting::create(['key' => 'bullet_2', 'value' => 'Temporary copy']);

        $this->actingAs($admin)
            ->put(route('admin.settings.auth-panel.update'), [
                'headline_1' => '   ',
                'bullet_2' => 'Fast, secure checkout',
            ])
            ->assertRedirect(route('admin.settings.auth-panel'));

        $this->getJson('/api/auth-panel')
            ->assertOk()
            ->assertJsonPath('data.headline_1', 'A calmer way')
            ->assertJsonPath('data.bullet_2', 'Fast, secure checkout');

        $this->actingAs($admin)
            ->put(route('admin.settings.auth-panel.update'), ['reset' => '1'])
            ->assertRedirect(route('admin.settings.auth-panel'));

        $this->assertSame(0, Setting::query()->count());

        $this->getJson('/api/auth-panel')
            ->assertOk()
            ->assertJsonPath('data.headline_1', 'A calmer way');
    }

    public function test_guests_and_non_admins_cannot_manage_the_content(): void
    {
        $user = User::factory()->create();

        $this->get(route('admin.settings.auth-panel'))->assertRedirect(route('admin.login'));

        $this->actingAs($user)
            ->get(route('admin.settings.auth-panel'))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.settings.auth-panel.update'), ['headline_1' => 'Hacked'])
            ->assertForbidden();
    }
}
