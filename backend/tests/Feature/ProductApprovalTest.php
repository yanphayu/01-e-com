<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
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
        $this->withoutMiddleware(ValidateCsrfToken::class);

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

        $this->actingAs($admin)
            ->post(route('admin.products.approve', $product))
            ->assertRedirect(route('admin.products.show', $product));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'approved', 'is_active' => true]);

        $this->getJson('/api/products')->assertOk()->assertJsonCount(1, 'data.data');
        $this->getJson("/api/products/{$product->id}")->assertOk()->assertJsonPath('data.status', 'approved');
    }

    public function test_admin_can_reject_product_and_it_stays_hidden(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

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

        $this->actingAs($admin)
            ->post(route('admin.products.reject', $product))
            ->assertRedirect(route('admin.products.show', $product));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'rejected', 'is_active' => false]);

        $this->getJson('/api/products')->assertOk()->assertJsonCount(0, 'data.data');
        $this->getJson("/api/products/{$product->id}")->assertNotFound();
    }

    public function test_non_admin_cannot_approve_product(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);

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

        $this->actingAs($user)
            ->post(route('admin.products.approve', $product))
            ->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'pending']);
    }

    public function test_admin_product_index_visibility_filter_treats_blank_as_all(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $subcategory = $this->makeSubcategory();

        $owner->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Visible listing',
            'slug' => 'visible-listing',
            'price' => 100,
            'status' => 'approved',
            'is_active' => true,
        ]);
        $owner->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Hidden listing',
            'slug' => 'hidden-listing',
            'price' => 200,
            'status' => 'approved',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Visible listing')
            ->assertSee('Hidden listing');

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['is_active' => '']))
            ->assertOk()
            ->assertSee('Visible listing')
            ->assertSee('Hidden listing');

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['is_active' => '1']))
            ->assertOk()
            ->assertSee('Visible listing')
            ->assertDontSee('Hidden listing');

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['is_active' => '0']))
            ->assertOk()
            ->assertSee('Hidden listing')
            ->assertDontSee('Visible listing');
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

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('Pending item');
    }

    public function test_store_endpoint_auto_approves_product_and_it_goes_live(): void
    {
        $user = $this->makeUser();
        $subcategory = $this->makeSubcategory();

        Sanctum::actingAs($user);
        $this->postJson('/api/products', [
            'subcategory_id' => $subcategory->id,
            'name' => 'Samsung S24',
            'price' => 800,
        ])->assertCreated()->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('products', ['name' => 'Samsung S24', 'status' => 'approved', 'is_active' => true]);
        $this->assertCount(1, $this->getJson('/api/products')->json('data.data'));
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

    public function test_public_listing_can_sort_products_by_date(): void
    {
        $user = $this->makeUser();
        $subcategory = $this->makeSubcategory();

        $user->products()->forceCreate([
            'subcategory_id' => $subcategory->id,
            'name' => 'Oldest item',
            'slug' => 'oldest-item',
            'description' => 'Posted first',
            'price' => 100,
            'status' => 'approved',
            'is_active' => true,
            'created_at' => now()->subDays(3),
        ]);
        $user->products()->forceCreate([
            'subcategory_id' => $subcategory->id,
            'name' => 'Middle item',
            'slug' => 'middle-item',
            'description' => 'Posted second',
            'price' => 200,
            'status' => 'approved',
            'is_active' => true,
            'created_at' => now()->subDays(2),
        ]);
        $user->products()->forceCreate([
            'subcategory_id' => $subcategory->id,
            'name' => 'Newest item',
            'slug' => 'newest-item',
            'description' => 'Posted last',
            'price' => 300,
            'status' => 'approved',
            'is_active' => true,
            'created_at' => now()->subDay(),
        ]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('data.data.0.name', 'Newest item')
            ->assertJsonPath('data.data.2.name', 'Oldest item');

        $this->getJson('/api/products?sort=latest')
            ->assertOk()
            ->assertJsonPath('data.data.0.name', 'Newest item');

        $this->getJson('/api/products?sort=oldest')
            ->assertOk()
            ->assertJsonPath('data.data.0.name', 'Oldest item')
            ->assertJsonPath('data.data.2.name', 'Newest item');
    }

    public function test_public_listing_can_filter_products_by_price(): void
    {
        $user = $this->makeUser();
        $subcategory = $this->makeSubcategory();

        foreach ([['Cheap item', 50], ['Mid item', 250], ['Expensive item', 900]] as [$name, $price]) {
            $user->products()->create([
                'subcategory_id' => $subcategory->id,
                'name' => $name,
                'slug' => str($name)->slug()->toString(),
                'description' => 'Test product',
                'price' => $price,
                'status' => 'approved',
                'is_active' => true,
            ]);
        }

        $this->getJson('/api/products?min_price=100')
            ->assertOk()
            ->assertJsonCount(2, 'data.data')
            ->assertJsonMissing(['name' => 'Cheap item']);

        $this->getJson('/api/products?max_price=250')
            ->assertOk()
            ->assertJsonCount(2, 'data.data')
            ->assertJsonMissing(['name' => 'Expensive item']);

        $this->getJson('/api/products?min_price=100&max_price=300')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Mid item');

        $this->getJson('/api/products?min_price=100&max_price=300&sort=oldest')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Mid item');
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
