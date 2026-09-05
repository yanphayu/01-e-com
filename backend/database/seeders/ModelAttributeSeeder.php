<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\ProductModel;
use Illuminate\Database\Seeder;

class ModelAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributeNames = Attribute::pluck('name', 'name')->toArray();

        $modelAttributeMap = [
            // Phones
            'iPhone 15' => ['Color', 'Storage', 'Battery'],
            'iPhone 15 Pro' => ['Color', 'Storage', 'Battery', 'Display'],
            'iPhone 15 Pro Max' => ['Color', 'Storage', 'Battery', 'Display'],
            'iPhone 14' => ['Color', 'Storage', 'Battery'],
            'iPhone 14 Pro' => ['Color', 'Storage', 'Battery', 'Display'],
            'Galaxy S24' => ['Color', 'Storage', 'RAM', 'Battery'],
            'Galaxy S24 Ultra' => ['Color', 'Storage', 'RAM', 'Battery', 'Display'],
            'Galaxy S23' => ['Color', 'Storage', 'RAM', 'Battery'],
            'Galaxy A54' => ['Color', 'Storage', 'RAM', 'Battery'],
            'Galaxy Z Fold' => ['Color', 'Storage', 'RAM', 'Battery'],
            'Galaxy Z Flip' => ['Color', 'Storage', 'RAM', 'Battery'],
            'Redmi Note 13' => ['Color', 'Storage', 'RAM', 'Battery'],
            'Poco X6' => ['Color', 'Storage', 'RAM', 'Battery'],
            'Find X7' => ['Color', 'Storage', 'RAM', 'Battery'],
            'V30' => ['Color', 'Storage', 'RAM', 'Battery'],
            'P60' => ['Color', 'Storage', 'RAM', 'Battery'],
            'Mate 60' => ['Color', 'Storage', 'RAM', 'Battery'],
            'Pixel 8 Pro' => ['Color', 'Storage', 'RAM', 'Battery'],

            // Laptops
            'MacBook Air' => ['Color', 'RAM', 'Storage', 'Processor', 'Weight'],
            'MacBook Pro' => ['Color', 'RAM', 'Storage', 'Processor', 'Weight', 'Display'],
            'ThinkPad X1' => ['Color', 'RAM', 'Storage', 'Processor', 'Weight'],
            'XPS 13' => ['Color', 'RAM', 'Storage', 'Processor', 'Weight'],
            'XPS 15' => ['Color', 'RAM', 'Storage', 'Processor', 'Weight', 'Display'],
            'Spectre x360' => ['Color', 'RAM', 'Storage', 'Processor', 'Weight'],
            'ROG Strix' => ['Color', 'RAM', 'Storage', 'Processor', 'Weight', 'Display'],
            'ZenBook' => ['Color', 'RAM', 'Storage', 'Processor', 'Weight'],
            'Legion Pro' => ['Color', 'RAM', 'Storage', 'Processor', 'Weight', 'Display'],

            // Tablets
            'iPad' => ['Color', 'Storage', 'Display'],
            'iPad Pro' => ['Color', 'Storage', 'Display'],
            'iPad Air' => ['Color', 'Storage', 'Display'],
            'Galaxy Tab S9' => ['Color', 'Storage', 'Display'],

            // Shoes
            'Air Jordan 1' => ['Color', 'Size', 'Material'],
            'Air Max' => ['Color', 'Size', 'Material'],
            'Air Force 1' => ['Color', 'Size', 'Material'],
            'Dunk' => ['Color', 'Size', 'Material'],
            'Ultraboost' => ['Color', 'Size', 'Material'],
            'Samba' => ['Color', 'Size', 'Material'],
            'Superstar' => ['Color', 'Size', 'Material'],
            'Suede' => ['Color', 'Size', 'Material'],
            'RS-X' => ['Color', 'Size', 'Material'],

            // Cars
            'Camry' => ['Color'],
            'Corolla' => ['Color'],
            'RAV4' => ['Color'],
            'Civic' => ['Color'],
            'CR-V' => ['Color'],
            'Ranger' => ['Color'],
            'Pilot Sport 4S' => ['Size', 'Weight'],

            // Skincare
            'Revitalift' => ['Weight'],
            'Sun SPF 50+' => ['Weight'],
            'Moisturizing Cream' => ['Weight'],
            'Niacinamide 10%' => ['Weight'],

            // Gaming
            'PlayStation 5' => ['Color', 'Storage'],
            'Switch OLED' => ['Color', 'Storage'],
        ];

        foreach ($modelAttributeMap as $modelName => $attrNames) {
            $model = ProductModel::where('name', $modelName)->first();
            if (! $model) {
                continue;
            }

            $attrIds = Attribute::whereIn('name', $attrNames)->pluck('id')->toArray();
            if ($attrIds) {
                $model->attributes()->sync($attrIds);
            }
        }
    }
}
