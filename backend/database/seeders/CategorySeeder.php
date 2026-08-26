<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Electronics' => [
                'slug' => 'electronics',
                'children' => ['Mobile Phones', 'Laptops', 'Tablets', 'Accessories'],
            ],
            'Clothing' => [
                'slug' => 'clothing',
                'children' => ["Men's Fashion", "Women's Fashion", 'Kids', 'Shoes'],
            ],
            'Home & Garden' => [
                'slug' => 'home-garden',
                'children' => ['Furniture', 'Kitchen', 'Garden', 'Decor'],
            ],
            'Vehicles' => [
                'slug' => 'vehicles',
                'children' => ['Cars', 'Motorcycles', 'Bicycles', 'Parts'],
            ],
            'Services' => [
                'slug' => 'services',
                'children' => ['Education', 'Health', 'Beauty', 'Repair'],
            ],
        ];

        $sortOrder = 0;

        foreach ($categories as $name => $data) {
            $parent = Category::create([
                'id' => (string) Str::uuid(),
                'name' => $name,
                'slug' => $data['slug'],
                'sort_order' => $sortOrder++,
                'is_active' => true,
            ]);

            $childSort = 0;
            foreach ($data['children'] as $childName) {
                Category::create([
                    'id' => (string) Str::uuid(),
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => $data['slug'] . '-' . Str::slug($childName),
                    'sort_order' => $childSort++,
                    'is_active' => true,
                ]);
            }
        }
    }
}
