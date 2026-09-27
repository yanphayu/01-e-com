<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_categories_as_pdf(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Category::create(['name' => 'Phones', 'slug' => 'phones']);
        Category::create(['name' => 'Laptops', 'slug' => 'laptops']);

        $response = $this->actingAs($admin)->get(route('admin.exports.index', [
            'resource' => 'categories',
            'format' => 'pdf',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('%PDF', (string) $response->getContent());
    }

    public function test_admin_can_export_categories_as_excel_csv(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Category::create(['name' => 'Phones', 'slug' => 'phones']);

        $response = $this->actingAs($admin)->get(route('admin.exports.index', [
            'resource' => 'categories',
            'format' => 'excel',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Phones', $csv);
        $this->assertStringContainsString('Subcategories', $csv);
    }

    public function test_export_respects_the_current_filters_unless_scope_is_all(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $category = Category::create(['name' => 'Phones', 'slug' => 'phones']);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Smartphones',
            'slug' => 'smartphones',
        ]);

        $owner->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Matching phone',
            'slug' => 'matching-phone',
            'price' => 100,
            'status' => 'approved',
        ]);
        $owner->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Other device',
            'slug' => 'other-device',
            'price' => 200,
            'status' => 'approved',
        ]);

        $filtered = $this->actingAs($admin)->get(route('admin.exports.index', [
            'resource' => 'products',
            'format' => 'excel',
            'search' => 'Matching',
        ]))->streamedContent();

        $this->assertStringContainsString('Matching phone', $filtered);
        $this->assertStringNotContainsString('Other device', $filtered);

        $all = $this->actingAs($admin)->get(route('admin.exports.index', [
            'resource' => 'products',
            'format' => 'excel',
            'scope' => 'all',
            'search' => 'Matching',
        ]))->streamedContent();

        $this->assertStringContainsString('Matching phone', $all);
        $this->assertStringContainsString('Other device', $all);
    }

    public function test_every_supported_resource_can_be_exported(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create(['name' => 'Phones', 'slug' => 'phones']);
        Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Smartphones',
            'slug' => 'smartphones',
        ]);

        foreach (['products', 'reports', 'users', 'categories', 'subcategories', 'brands', 'models', 'attributes'] as $resource) {
            $this->actingAs($admin)
                ->get(route('admin.exports.index', ['resource' => $resource, 'format' => 'excel']))
                ->assertOk();
        }
    }

    public function test_unknown_resources_and_formats_are_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/exports/secrets?format=excel')
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.exports.index', ['resource' => 'users', 'format' => 'exe']))
            ->assertNotFound();
    }

    public function test_non_admins_cannot_export(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.exports.index', ['resource' => 'users', 'format' => 'excel']))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_the_admin_login(): void
    {
        $this->get(route('admin.exports.index', ['resource' => 'users', 'format' => 'excel']))
            ->assertRedirect(route('admin.login'));
    }

    public function test_exported_products_include_seller_and_category_columns(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create(['name' => 'Seller Person']);
        $category = Category::create(['name' => 'Phones', 'slug' => 'phones']);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Smartphones',
            'slug' => 'smartphones',
        ]);

        Product::create([
            'user_id' => $owner->id,
            'subcategory_id' => $subcategory->id,
            'name' => 'Exported phone',
            'slug' => 'exported-phone',
            'price' => 999,
            'status' => 'approved',
            'is_active' => true,
        ]);

        $csv = $this->actingAs($admin)->get(route('admin.exports.index', [
            'resource' => 'products',
            'format' => 'excel',
        ]))->streamedContent();

        $this->assertStringContainsString('Exported phone', $csv);
        $this->assertStringContainsString('Seller Person', $csv);
        $this->assertStringContainsString('Phones', $csv);
    }
}
