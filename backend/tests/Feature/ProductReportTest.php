<?php

namespace Tests\Feature;

use App\Events\NotificationCreated;
use App\Models\Category;
use App\Models\Product;
use App\Models\Report;
use App\Models\Subcategory;
use App\Models\User;
use App\Notifications\ProductReported;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_report_a_product_and_admins_are_notified(): void
    {
        $reporter = $this->makeUser();
        $owner = $this->makeUser();
        $admin = $this->makeUser(['is_admin' => true]);
        $product = $this->makeProduct($owner);

        Sanctum::actingAs($reporter);

        $this->postJson("/api/products/{$product->id}/reports", [
            'reason' => 'fraud',
            'details' => 'Looks like a scam listing.',
        ])->assertCreated()->assertJsonPath('data.reason', 'fraud');

        $this->assertDatabaseHas('reports', [
            'user_id' => $reporter->id,
            'product_id' => $product->id,
            'reason' => 'fraud',
            'status' => 'pending',
        ]);

        $admin->refresh();
        $notification = $admin->notifications()->first();

        $this->assertNotNull($notification);
        $this->assertSame($product->id, $notification->data['product_id']);
        $this->assertSame('fraud', $notification->data['reason']);
    }

    public function test_user_cannot_report_own_product(): void
    {
        $owner = $this->makeUser();
        $product = $this->makeProduct($owner);

        Sanctum::actingAs($owner);

        $this->postJson("/api/products/{$product->id}/reports", [
            'reason' => 'spam',
        ])->assertStatus(422)->assertJsonPath('success', false);

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_duplicate_pending_report_is_blocked(): void
    {
        $reporter = $this->makeUser();
        $owner = $this->makeUser();
        $product = $this->makeProduct($owner);

        Report::create([
            'user_id' => $reporter->id,
            'product_id' => $product->id,
            'reason' => 'spam',
        ]);

        Sanctum::actingAs($reporter);

        $this->postJson("/api/products/{$product->id}/reports", [
            'reason' => 'fraud',
        ])->assertStatus(422)->assertJsonPath('success', false);

        $this->assertDatabaseCount('reports', 1);
    }

    public function test_guest_cannot_report_a_product(): void
    {
        $owner = $this->makeUser();
        $product = $this->makeProduct($owner);

        $this->postJson("/api/products/{$product->id}/reports", [
            'reason' => 'spam',
        ])->assertStatus(401);
    }

    public function test_admin_can_resolve_a_report(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $admin = $this->makeUser(['is_admin' => true]);
        $owner = $this->makeUser();
        $reporter = $this->makeUser();
        $product = $this->makeProduct($owner);

        $report = Report::create([
            'user_id' => $reporter->id,
            'product_id' => $product->id,
            'reason' => 'fraud',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.reports.resolve', $report), ['status' => 'resolved'])
            ->assertRedirect();

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'resolved']);
    }

    public function test_opening_reports_marks_report_notifications_as_read(): void
    {
        $admin = $this->makeUser(['is_admin' => true]);
        $owner = $this->makeUser();
        $reporter = $this->makeUser();
        $product = $this->makeProduct($owner);
        $report = Report::create([
            'user_id' => $reporter->id,
            'product_id' => $product->id,
            'reason' => 'fraud',
        ]);

        $admin->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ProductReported::class,
            'data' => [
                'id' => $report->id,
                'type' => 'product_reported',
                'product_id' => $product->id,
                'product_name' => $product->name,
            ],
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.notifications.unread-count'))
            ->assertOk()
            ->assertJsonPath('count', 1);

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('data-admin-report-unread-count', false)
            ->assertDontSee('data-bell-toggle', false);

        $this->assertSame(0, $admin->unreadNotifications()
            ->where('type', ProductReported::class)
            ->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = $this->makeUser();
        $otherUser = $this->makeUser();

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ProductReported::class,
            'data' => ['product_id' => 1],
        ]);
        $readNotification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ProductReported::class,
            'data' => ['product_id' => 2],
            'read_at' => now(),
        ]);
        $otherNotification = $otherUser->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ProductReported::class,
            'data' => ['product_id' => 3],
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertNotNull($readNotification->fresh()->read_at);
        $this->assertNull($otherNotification->fresh()->read_at);
    }

    public function test_notifications_are_broadcast_immediately_without_a_queue(): void
    {
        Event::fake([NotificationCreated::class]);

        $reporter = $this->makeUser();
        $owner = $this->makeUser();
        $admin = $this->makeUser(['is_admin' => true]);
        $product = $this->makeProduct($owner);

        Sanctum::actingAs($reporter);

        $this->postJson("/api/products/{$product->id}/reports", ['reason' => 'fraud'])->assertCreated();

        $this->assertDatabaseCount('notifications', 1);
        $this->assertSame(1, $admin->notifications()->count());

        Event::assertDispatched(
            NotificationCreated::class,
            function (NotificationCreated $event) use ($admin, $product) {
                return $event->recipientId === $admin->id
                    && $event->broadcastAs() === 'notification.created'
                    && $event->broadcastOn()[0]->name === 'private-App.Models.User.'.$admin->id
                    && $event->payload['type'] === 'product_reported'
                    && $event->payload['product_id'] === $product->id;
            },
        );

        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_user_can_clear_read_notifications(): void
    {
        $user = $this->makeUser();
        $otherUser = $this->makeUser();

        $readNotification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ProductReported::class,
            'data' => ['product_id' => 1],
            'read_at' => now(),
        ]);
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ProductReported::class,
            'data' => ['product_id' => 2],
        ]);
        $otherNotification = $otherUser->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ProductReported::class,
            'data' => ['product_id' => 3],
            'read_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/notifications/read')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.deleted', 1);

        $this->assertDatabaseMissing('notifications', ['id' => $readNotification->id]);
        $this->assertSame(1, $user->unreadNotifications()->count());
        $this->assertDatabaseHas('notifications', ['id' => $otherNotification->id]);
    }

    public function test_guest_cannot_clear_notifications(): void
    {
        $this->deleteJson('/api/notifications/read')->assertUnauthorized();
    }

    public function test_admin_can_search_and_filter_reports(): void
    {
        $admin = $this->makeUser(['is_admin' => true]);
        $owner = $this->makeUser();
        $reporter = $this->makeUser();
        $product = $this->makeProduct($owner);

        Report::create([
            'user_id' => $reporter->id,
            'product_id' => $product->id,
            'reason' => 'Counterfeit item',
            'details' => 'The serial number does not match the listing.',
            'status' => 'pending',
        ]);
        Report::create([
            'user_id' => $reporter->id,
            'product_id' => $product->id,
            'reason' => 'Duplicate listing',
            'status' => 'resolved',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.reports.index', [
                'search' => 'Counterfeit',
                'status' => 'pending',
            ]))
            ->assertOk()
            ->assertSee('Counterfeit item')
            ->assertSee('The serial number does not match the listing.')
            ->assertDontSee('Duplicate listing');
    }

    public function test_non_admin_cannot_access_reports(): void
    {
        $user = $this->makeUser(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.reports.index'))
            ->assertForbidden();
    }

    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ], $overrides));
    }

    private function makeProduct(User $owner): Product
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Phones',
            'slug' => 'phones',
        ]);

        return $owner->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'description' => 'Brand new',
            'price' => 999,
            'status' => 'approved',
            'is_active' => true,
        ]);
    }
}
