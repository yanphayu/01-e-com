<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_posted_product_starts_as_pending_and_is_hidden_from_public_listing(): void
    {
        $user = $this->makeUser();
        $subcategory = $this->makeSubcategory();

        $product = $user->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'description' => 'Brand new',
            'price' => 999,
            'status' => 'pending',
        ]);

        $this->assertSame('pending', $product->status);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'pending']);

        $response = $this->getJson('/api/products');
        $response->assertOk();
        $this->assertCount(0, $response->json('data.data'));

        $this->getJson("/api/products/{$product->id}")->assertNotFound();
        $this->getJson('/api/search?q=iPhone')->assertOk()->assertJsonCount(0, 'data.products');
    }

    public function test_admin_can_approve_product_and_it_goes_live(): void
    {
        $owner = $this->makeUser();
        $admin = $this->makeUser(['is_admin' => true]);
        $subcategory = $this->makeSubcategory();

        $product = $owner->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'description' => 'Brand new',
            'price' => 999,
            'status' => 'pending',
        ]);

        $this->assertCount(0, $this->getJson('/api/products')->json('data.data'));

        Sanctum::actingAs($admin);
        $this->postJson("/api/admin/products/{$product->id}/approve")->assertOk()->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'approved', 'is_active' => true]);

        $this->getJson('/api/products')->assertOk()->assertJsonCount(1, 'data.data');
        $this->getJson("/api/products/{$product->id}")->assertOk()->assertJsonPath('data.status', 'approved');
    }

    public function test_admin_can_reject_product_and_it_stays_hidden(): void
    {
        $owner = $this->makeUser();
        $admin = $this->makeUser(['is_admin' => true]);
        $subcategory = $this->makeSubcategory();

        $product = $owner->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'description' => 'Brand new',
            'price' => 999,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);
        $this->postJson("/api/admin/products/{$product->id}/reject")->assertOk()->assertJsonPath('data.status', 'rejected');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'rejected', 'is_active' => false]);

        $this->getJson('/api/products')->assertOk()->assertJsonCount(0, 'data.data');
        $this->getJson("/api/products/{$product->id}")->assertNotFound();
    }

    public function test_non_admin_cannot_approve_product(): void
    {
        $user = $this->makeUser(['is_admin' => false]);
        $admin = $this->makeUser(['is_admin' => true]);
        $owner = $this->makeUser();
        $subcategory = $this->makeSubcategory();

        $product = $owner->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'description' => 'Brand new',
            'price' => 999,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);
        $this->postJson("/api/admin/products/{$product->id}/approve")->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'pending']);
    }

    public function test_admin_product_index_filters_by_status(): void
    {
        $admin = $this->makeUser(['is_admin' => true]);
        $owner = $this->makeUser();
        $subcategory = $this->makeSubcategory();

        $owner->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Pending item',
            'slug' => 'pending-item',
            'description' => null,
            'price' => 100,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);
        $this->getJson('/api/admin/products?status=pending')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.status', 'pending');
    }

    public function test_store_endpoint_creates_product_as_pending(): void
    {
        $user = $this->makeUser();
        $subcategory = $this->makeSubcategory();

        Sanctum::actingAs($user);
        $this->postJson('/api/products', [
            'subcategory_id' => $subcategory->id,
            'name' => 'Samsung S24',
            'price' => 800,
        ])->assertCreated()->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('products', ['name' => 'Samsung S24', 'status' => 'pending']);
        $this->assertCount(0, $this->getJson('/api/products')->json('data.data'));
    }

    public function test_admin_posted_product_is_auto_approved_and_live_on_frontend(): void
    {
        $admin = $this->makeUser(['is_admin' => true]);
        $subcategory = $this->makeSubcategory();

        Sanctum::actingAs($admin);
        $this->postJson('/api/products', [
            'subcategory_id' => $subcategory->id,
            'name' => 'Admin iPhone 16',
            'price' => 1200,
        ])->assertCreated()->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('products', ['name' => 'Admin iPhone 16', 'status' => 'approved', 'is_active' => true]);

        $this->getJson('/api/products')->assertOk()->assertJsonCount(1, 'data.data');
        $this->getJson('/api/products')->assertJsonPath('data.data.0.name', 'Admin iPhone 16');
    }

    private function makeUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ], $overrides));
    }

    private function makeSubcategory(): Subcategory
    {
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        return Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Phones',
            'slug' => 'phones',
        ]);
    }
}
