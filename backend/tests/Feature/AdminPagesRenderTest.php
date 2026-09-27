<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductModel;
use App\Models\Report;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_admin_pages_render_for_an_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();

        $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Phones',
            'slug' => 'phones',
            'has_brand' => true,
            'has_model' => true,
        ]);
        $brand = Brand::create(['subcategory_id' => $subcategory->id, 'name' => 'Apple']);
        $attribute = Attribute::create(['name' => 'Storage']);
        $model = ProductModel::create(['brand_id' => $brand->id, 'name' => 'iPhone 15']);
        $model->attributes()->attach($attribute->id);

        $product = $owner->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'iPhone 15 Pro',
            'slug' => 'iphone-15-pro',
            'description' => 'Brand new',
            'price' => 1300,
            'status' => 'pending',
        ]);

        Report::create([
            'user_id' => $owner->id,
            'product_id' => $product->id,
            'reason' => 'fraud',
        ]);

        $pages = [
            'admin.dashboard',
            'admin.analytics',
            'admin.products.index',
            'admin.users.index',
            'admin.reports.index',
            'admin.ads.index',
            'admin.ads.create',
            'admin.categories.index',
            'admin.categories.create',
            'admin.subcategories.index',
            'admin.subcategories.create',
            'admin.brands.index',
            'admin.brands.create',
            'admin.models.index',
            'admin.models.create',
            'admin.attributes.index',
            'admin.attributes.create',
            'admin.settings.auth-panel',
        ];
        $infiniteScrollTargets = [
            'admin.products.index' => 'admin-products-table-body',
            'admin.users.index' => 'admin-users-table-body',
            'admin.reports.index' => 'admin-reports-table-body',
            'admin.categories.index' => 'admin-categories-table-body',
            'admin.subcategories.index' => 'admin-subcategories-table-body',
            'admin.brands.index' => 'admin-brands-table-body',
            'admin.models.index' => 'admin-models-table-body',
            'admin.attributes.index' => 'admin-attributes-table-body',
        ];

        foreach ($pages as $route) {
            $response = $this->actingAs($admin)->get(route($route));
            $response->assertOk();
            $response->assertDontSee('— TRINITY', false);

            if (isset($infiniteScrollTargets[$route])) {
                $response
                    ->assertSee('data-admin-infinite-scroll', false)
                    ->assertSee('data-infinite-scroll-target="'.$infiniteScrollTargets[$route].'"', false)
                    ->assertDontSee('rel="next"', false);
            }

            if ($route === 'admin.dashboard') {
                $response
                    ->assertSee('data-admin-activity-chart', false)
                    ->assertSee('data-admin-chat-chart', false)
                    ->assertSee('admin-activity-chart-data', false)
                    ->assertSee('admin-chat-chart-data', false);
            }

            if ($route === 'admin.analytics') {
                $response
                    ->assertSee('data-admin-activity-chart', false)
                    ->assertSee('data-admin-listing-status-chart', false)
                    ->assertSee('data-admin-report-status-chart', false)
                    ->assertSee('data-admin-category-chart', false)
                    ->assertSee('data-admin-conversation-chart', false)
                    ->assertSee('data-admin-cumulative-chart', false)
                    ->assertSee('admin-activity-chart-data', false)
                    ->assertSee('admin-analytics-chart-data', false);
            }
        }

        $this->actingAs($admin)->get(route('admin.products.show', $product))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.show', $owner))
            ->assertOk()
            ->assertSee('data-admin-user-activity-chart', false)
            ->assertSee('admin-user-activity-chart-data', false);

        $this->actingAs($admin)->get(route('admin.categories.edit', $category))->assertOk();
        $this->actingAs($admin)->get(route('admin.subcategories.edit', $subcategory))->assertOk();
        $this->actingAs($admin)->get(route('admin.brands.edit', $brand))->assertOk();
        $this->actingAs($admin)->get(route('admin.models.edit', $model))->assertOk();
        $this->actingAs($admin)->get(route('admin.attributes.edit', $attribute))->assertOk();
    }

    public function test_analytics_uses_the_selected_range(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create(['created_at' => now()->subDays(80)]);
        $category = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => 'Phones',
            'slug' => 'phones',
        ]);

        $product = $owner->products()->create([
            'subcategory_id' => $subcategory->id,
            'name' => 'Older phone',
            'slug' => 'older-phone',
            'description' => 'Created outside the seven-day range',
            'price' => 500,
            'status' => 'approved',
        ]);
        $product->forceFill(['created_at' => now()->subDays(40)])->save();

        foreach ([7, 30, 90] as $range) {
            $content = $this->actingAs($admin)
                ->get(route('admin.analytics', ['range' => $range]))
                ->assertOk()
                ->getContent();

            $this->assertIsString($content);
            $this->assertSame(
                1,
                preg_match('/<script id="admin-analytics-chart-data" type="application\/json">(.*?)<\/script>/s', $content, $matches),
            );

            $chartData = json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);

            $this->assertCount($range, $chartData['activity']['labels']);
            $this->assertSame($range === 90 ? 2 : 1, array_sum($chartData['activity']['users']));
            $this->assertSame($range === 90 ? 1 : 0, array_sum($chartData['activity']['products']));
        }
    }
}
