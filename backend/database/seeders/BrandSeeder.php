<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Apple' => ['iPhone 15', 'iPhone 15 Pro', 'iPhone 15 Pro Max', 'iPhone 14', 'iPhone 14 Pro', 'MacBook Air', 'MacBook Pro', 'iPad', 'iPad Pro', 'AirPods', 'Apple Watch'],
            'Samsung' => ['Galaxy S24', 'Galaxy S24 Ultra', 'Galaxy S23', 'Galaxy A54', 'Galaxy Z Fold', 'Galaxy Z Flip', 'Galaxy Tab', 'Galaxy Watch'],
            'Xiaomi' => ['Redmi Note 13', 'Redmi 13', 'Poco X6', '14', '14 Pro', 'Mi Band'],
            'Oppo' => ['Find X7', 'Reno 11', 'A78', 'A38'],
            'Vivo' => ['V30', 'V30 Pro', 'Y36', 'Y27'],
            'Huawei' => ['P60', 'Mate 60', 'Nova 12', 'Watch GT'],
            'Sony' => ['PlayStation 5', 'WH-1000XM5', 'WF-1000XM5', 'Xperia 1 VI'],
            'Nike' => ['Air Max', 'Air Jordan', 'Air Force 1', 'Dunk', 'Pegasus'],
            'Adidas' => ['Ultraboost', 'Superstar', 'Stan Smith', 'Gazelle', 'Samba'],
            'Puma' => ['Suede', 'RS-X', 'Calico', 'Clyde'],
            'Lenovo' => ['ThinkPad X1', 'IdeaPad Slim', 'Legion Pro', 'Yoga 9i'],
            'Dell' => ['XPS 13', 'XPS 15', 'Inspiron', 'Latitude'],
            'HP' => ['Spectre x360', 'Envy', 'Pavilion', 'Omen'],
            'Asus' => ['ROG Strix', 'ZenBook', 'VivoBook', 'TUF Gaming'],
            'Toyota' => ['Camry', 'Corolla', 'RAV4', 'Hilux', 'Land Cruiser'],
            'Honda' => ['Civic', 'CR-V', 'HR-V', 'Accord'],
            'Ford' => ['Ranger', 'Everest', 'Territory', 'Bronco'],
            'LG' => ['OLED TV', 'Gram', 'Washing Machine', 'Refrigerator'],
            'L\'Oreal' => ['Revitalift', 'True Match', 'Elseve'],
            'Nivea' => ['Soft', 'Creme', 'Sun', 'Men'],
        ];

        foreach ($brands as $brandName => $models) {
            $brand = Brand::create(['name' => $brandName]);
            foreach ($models as $modelName) {
                $brand->models()->create(['name' => $modelName]);
            }
        }
    }
}
