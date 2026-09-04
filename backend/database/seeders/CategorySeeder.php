<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Gadgets, devices, and electronic accessories',
                'subcategories' => [
                    ['name' => 'Phones', 'slug' => 'phones', 'has_brand' => true, 'has_model' => true],
                    ['name' => 'Laptops', 'slug' => 'laptops', 'has_brand' => true, 'has_model' => true],
                    ['name' => 'Tablets', 'slug' => 'tablets', 'has_brand' => true, 'has_model' => true],
                    ['name' => 'Accessories', 'slug' => 'accessories', 'has_brand' => true, 'has_model' => false],
                ],
            ],
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'description' => 'Clothing, shoes, and fashion accessories',
                'subcategories' => [
                    ['name' => 'Men', 'slug' => 'men', 'has_brand' => true, 'has_model' => false],
                    ['name' => 'Women', 'slug' => 'women', 'has_brand' => true, 'has_model' => false],
                    ['name' => 'Kids', 'slug' => 'kids', 'has_brand' => true, 'has_model' => false],
                    ['name' => 'Shoes', 'slug' => 'shoes', 'has_brand' => true, 'has_model' => true],
                ],
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'description' => 'Furniture, decor, and garden supplies',
                'subcategories' => [
                    ['name' => 'Furniture', 'slug' => 'furniture', 'has_brand' => true, 'has_model' => false],
                    ['name' => 'Decor', 'slug' => 'decor', 'has_brand' => false, 'has_model' => false],
                    ['name' => 'Kitchen', 'slug' => 'kitchen', 'has_brand' => true, 'has_model' => false],
                ],
            ],
            [
                'name' => 'Sports & Outdoors',
                'slug' => 'sports-outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'subcategories' => [
                    ['name' => 'Gym Equipment', 'slug' => 'gym-equipment', 'has_brand' => true, 'has_model' => true],
                    ['name' => 'Cycling', 'slug' => 'cycling', 'has_brand' => true, 'has_model' => true],
                    ['name' => 'Camping', 'slug' => 'camping', 'has_brand' => true, 'has_model' => false],
                ],
            ],
            [
                'name' => 'Books & Media',
                'slug' => 'books-media',
                'description' => 'Books, movies, music, and games',
                'subcategories' => [
                    ['name' => 'Books', 'slug' => 'books', 'has_brand' => false, 'has_model' => false],
                    ['name' => 'Movies', 'slug' => 'movies', 'has_brand' => false, 'has_model' => false],
                    ['name' => 'Games', 'slug' => 'games', 'has_brand' => true, 'has_model' => true],
                ],
            ],
            [
                'name' => 'Beauty & Health',
                'slug' => 'beauty-health',
                'description' => 'Skincare, makeup, and health products',
                'subcategories' => [
                    ['name' => 'Skincare', 'slug' => 'skincare', 'has_brand' => true, 'has_model' => false],
                    ['name' => 'Makeup', 'slug' => 'makeup', 'has_brand' => true, 'has_model' => false],
                    ['name' => 'Supplements', 'slug' => 'supplements', 'has_brand' => true, 'has_model' => false],
                ],
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'description' => 'Car parts, accessories, and tools',
                'subcategories' => [
                    ['name' => 'Parts', 'slug' => 'parts', 'has_brand' => true, 'has_model' => true],
                    ['name' => 'Accessories', 'slug' => 'auto-accessories', 'has_brand' => true, 'has_model' => false],
                    ['name' => 'Tools', 'slug' => 'auto-tools', 'has_brand' => true, 'has_model' => false],
                ],
            ],
            [
                'name' => 'Toys & Hobbies',
                'slug' => 'toys-hobbies',
                'description' => 'Toys, collectibles, and hobby supplies',
                'subcategories' => [
                    ['name' => 'Toys', 'slug' => 'toys', 'has_brand' => true, 'has_model' => false],
                    ['name' => 'Collectibles', 'slug' => 'collectibles', 'has_brand' => true, 'has_model' => false],
                    ['name' => 'Model Kits', 'slug' => 'model-kits', 'has_brand' => true, 'has_model' => true],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $subcategories = $categoryData['subcategories'];
            unset($categoryData['subcategories']);

            $category = Category::create($categoryData);

            foreach ($subcategories as $subcategory) {
                $category->subcategories()->create($subcategory);
            }
        }
    }
}
