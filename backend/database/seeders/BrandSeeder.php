<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brandData = [
            'phones' => [
                'Apple' => ['iPhone 15', 'iPhone 15 Pro', 'iPhone 15 Pro Max', 'iPhone 14', 'iPhone 14 Pro'],
                'Samsung' => ['Galaxy S24', 'Galaxy S24 Ultra', 'Galaxy S23', 'Galaxy A54', 'Galaxy Z Fold', 'Galaxy Z Flip'],
                'Xiaomi' => ['Redmi Note 13', 'Redmi 13', 'Poco X6', '14', '14 Pro'],
                'Oppo' => ['Find X7', 'Reno 11', 'A78', 'A38'],
                'Vivo' => ['V30', 'V30 Pro', 'Y36', 'Y27'],
                'Huawei' => ['P60', 'Mate 60', 'Nova 12'],
            ],
            'laptops' => [
                'Lenovo' => ['ThinkPad X1', 'IdeaPad Slim', 'Legion Pro', 'Yoga 9i'],
                'Dell' => ['XPS 13', 'XPS 15', 'Inspiron', 'Latitude'],
                'HP' => ['Spectre x360', 'Envy', 'Pavilion', 'Omen'],
                'Asus' => ['ROG Strix', 'ZenBook', 'VivoBook', 'TUF Gaming'],
                'Apple' => ['MacBook Air', 'MacBook Pro'],
            ],
            'tablets' => [
                'Apple' => ['iPad', 'iPad Pro', 'iPad Air'],
                'Samsung' => ['Galaxy Tab S9', 'Galaxy Tab A9'],
            ],
            'shoes' => [
                'Nike' => ['Air Max', 'Air Jordan', 'Air Force 1', 'Dunk', 'Pegasus'],
                'Adidas' => ['Ultraboost', 'Superstar', 'Stan Smith', 'Gazelle', 'Samba'],
                'Puma' => ['Suede', 'RS-X', 'Calico', 'Clyde'],
            ],
            'games' => [
                'Sony' => ['PlayStation 5', 'PlayStation 5 Slim'],
            ],
            'skincare' => [
                'L\'Oreal' => ['Revitalift', 'True Match', 'Elseve'],
                'Nivea' => ['Soft', 'Creme', 'Sun', 'Men'],
            ],
            'parts' => [
                'Toyota' => ['Camry', 'Corolla', 'RAV4', 'Hilux', 'Land Cruiser'],
                'Honda' => ['Civic', 'CR-V', 'HR-V', 'Accord'],
                'Ford' => ['Ranger', 'Everest', 'Territory', 'Bronco'],
            ],
        ];

        foreach ($brandData as $subcategorySlug => $brands) {
            $subcategory = Subcategory::where('slug', $subcategorySlug)->first();
            if (! $subcategory) {
                continue;
            }

            foreach ($brands as $brandName => $models) {
                $brand = Brand::create([
                    'name' => $brandName,
                    'subcategory_id' => $subcategory->id,
                ]);

                foreach ($models as $modelName) {
                    $brand->models()->create(['name' => $modelName]);
                }
            }
        }
    }
}
