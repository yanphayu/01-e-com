<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Report;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
        $admin = $this->makeUser(['is_admin' => true]);
        $owner = $this->makeUser();
        $reporter = $this->makeUser();
        $product = $this->makeProduct($owner);

        $report = Report::create([
            'user_id' => $reporter->id,
            'product_id' => $product->id,
            'reason' => 'fraud',
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/reports/{$report->id}/resolve", [
            'status' => 'resolved',
        ])->assertOk()->assertJsonPath('data.status', 'resolved');

        $this->assertDatabaseHas('reports', ['id' => $report->id, 'status' => 'resolved']);
    }

    public function test_non_admin_cannot_access_reports(): void
    {
        $user = $this->makeUser(['is_admin' => false]);

        Sanctum::actingAs($user);

        $this->getJson('/api/admin/reports')->assertForbidden();
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
