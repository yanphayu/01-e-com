<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\SellerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'seller_id' => SellerProfile::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 5, 500),
            'compare_price' => fake()->optional(0.3)->randomFloat(2, 5, 800),
            'stock' => fake()->numberBetween(0, 100),
            'condition' => fake()->randomElement(['new', 'used']),
            'location' => 'Phnom Penh',
            'city' => 'Phnom Penh',
        ];
    }
}
