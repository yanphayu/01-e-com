<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use App\Notifications\NewProductCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NewProductCreatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_are_notified_when_a_product_is_created(): void
    {
        Notification::fake();

        $seller = $this->makeUser();
        $admin1 = $this->makeUser(['is_admin' => true]);
        $admin2 = $this->makeUser(['is_admin' => true]);

        Sanctum::actingAs($seller);

        $subcategory = $this->makeSubcategory();

        $this->postJson('/api/products', [
            'subcategory_id' => $subcategory->id,
            'name' => 'Vintage Camera',
            'price' => 120,
            'description' => 'Great condition',
        ])->assertCreated()->assertJsonPath('success', true);

        Notification::assertSentTo($admin1, NewProductCreated::class);
        Notification::assertSentTo($admin2, NewProductCreated::class);
    }

    public function test_non_admins_are_not_notified_when_a_product_is_created(): void
    {
        Notification::fake();

        $seller = $this->makeUser();
        $regular = $this->makeUser();

        Sanctum::actingAs($seller);

        $subcategory = $this->makeSubcategory();

        $this->postJson('/api/products', [
            'subcategory_id' => $subcategory->id,
            'name' => 'Vintage Camera',
            'price' => 120,
        ])->assertCreated();

        Notification::assertNotSentTo($regular, NewProductCreated::class);
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
            'name' => 'Cameras',
            'slug' => 'cameras',
        ]);
    }
}
