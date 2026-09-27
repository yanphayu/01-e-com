<?php

namespace Tests\Feature;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_endpoint_returns_only_active_ads_for_the_requested_placement(): void
    {
        Ad::factory()->create(['placement' => Ad::PLACEMENT_TOP, 'headline' => 'Top only']);
        Ad::factory()->create(['placement' => Ad::PLACEMENT_HOME, 'headline' => 'Home only']);
        Ad::factory()->create(['placement' => Ad::PLACEMENT_BOTH, 'headline' => 'Everywhere']);
        Ad::factory()->inactive()->create(['placement' => Ad::PLACEMENT_BOTH, 'headline' => 'Hidden']);

        $this->getJson('/api/ads?placement=top')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['headline' => 'Top only'])
            ->assertJsonFragment(['headline' => 'Everywhere'])
            ->assertJsonMissing(['headline' => 'Home only'])
            ->assertJsonMissing(['headline' => 'Hidden']);

        $this->getJson('/api/ads?placement=home')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing(['headline' => 'Top only']);
    }

    public function test_public_endpoint_orders_ads_by_sort_order(): void
    {
        Ad::factory()->create(['sort_order' => 5, 'headline' => 'Second']);
        Ad::factory()->create(['sort_order' => 1, 'headline' => 'First']);

        $this->getJson('/api/ads')
            ->assertOk()
            ->assertJsonPath('data.0.headline', 'First')
            ->assertJsonPath('data.1.headline', 'Second');
    }

    public function test_admin_can_create_an_ad_with_an_uploaded_image(): void
    {
        Storage::fake('public');
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.ads.store'), [
                'headline' => 'Autumn sale',
                'subtext' => 'Up to 40% off',
                'button_label' => 'Browse deals',
                'link_url' => '/products',
                'image' => UploadedFile::fake()->create('banner.jpg', 100, 'image/jpeg'),
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.ads.index'))
            ->assertSessionHas('status');

        $ad = Ad::query()->firstOrFail();

        $this->assertSame('Autumn sale', $ad->headline);
        $this->assertSame(Ad::PLACEMENT_HOME, $ad->placement);
        $this->assertTrue($ad->is_active);
        $this->assertSame(1, $ad->sort_order);
        Storage::disk('public')->assertExists($ad->image);

        $this->getJson('/api/ads?placement=home')
            ->assertOk()
            ->assertJsonPath('data.0.image', $ad->image);
    }

    public function test_admin_can_create_an_ad_without_a_headline(): void
    {
        Storage::fake('public');
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.ads.store'), [
                'image' => UploadedFile::fake()->create('banner.jpg', 100, 'image/jpeg'),
                'button_label' => 'Look inside',
            ])
            ->assertRedirect(route('admin.ads.index'))
            ->assertSessionHasNoErrors();

        $ad = Ad::query()->firstOrFail();

        $this->assertNull($ad->headline);
        $this->assertSame('Look inside', $ad->button_label);
    }

    public function test_admin_can_replace_an_ad_image_and_the_old_file_is_removed(): void
    {
        Storage::fake('public');
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $admin = User::factory()->create(['is_admin' => true]);
        $ad = Ad::factory()->create(['headline' => 'Original']);

        $originalImage = $ad->image;
        Storage::disk('public')->put($originalImage, 'original');

        $this->actingAs($admin)
            ->put(route('admin.ads.update', $ad), [
                'headline' => 'Replaced',
                'image' => UploadedFile::fake()->create('replacement.png', 100, 'image/png'),
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.ads.index'));

        $ad->refresh();

        $this->assertNotSame($originalImage, $ad->image);
        Storage::disk('public')->assertExists($ad->image);
        Storage::disk('public')->assertMissing($originalImage);

        $this->getJson('/api/ads')
            ->assertOk()
            ->assertJsonPath('data.0.image', $ad->image);
    }

    public function test_admin_can_edit_toggle_and_delete_ads(): void
    {
        Storage::fake('public');
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $admin = User::factory()->create(['is_admin' => true]);
        $ad = Ad::factory()->create(['headline' => 'Original', 'is_active' => true]);

        $this->actingAs($admin)
            ->put(route('admin.ads.update', $ad), [
                'headline' => 'Updated',
            ])
            ->assertRedirect(route('admin.ads.index'));

        $ad->refresh();
        $this->assertSame('Updated', $ad->headline);
        $this->assertSame(Ad::PLACEMENT_BOTH, $ad->placement);
        $this->assertFalse($ad->is_active);

        $this->actingAs($admin)
            ->post(route('admin.ads.toggle-active', $ad))
            ->assertRedirect();

        $this->assertTrue($ad->fresh()->is_active);

        $this->actingAs($admin)
            ->delete(route('admin.ads.destroy', $ad))
            ->assertRedirect(route('admin.ads.index'));

        $this->assertDatabaseCount('ads', 0);
    }

    public function test_non_admins_cannot_manage_ads(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $user = User::factory()->create();
        $ad = Ad::factory()->create();

        $this->actingAs($user)->get(route('admin.ads.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.ads.create'))->assertForbidden();
        $this->actingAs($user)->delete(route('admin.ads.destroy', $ad))->assertForbidden();
    }
}
