<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'Color', 'RAM', 'Storage', 'Size', 'Material', 'Weight', 'Display', 'Processor', 'Battery',
        ];

        foreach ($attributes as $name) {
            Attribute::create(['name' => $name]);
        }
    }
}
