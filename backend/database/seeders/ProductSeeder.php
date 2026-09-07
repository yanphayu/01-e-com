<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductDetail;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private int $userId = 2;

    private array $products = [
        [
            'name' => 'iPhone 15 Pro Max 256GB',
            'subcategory_id' => 1,
            'price' => 1199.00,
            'description' => 'Brand new sealed iPhone 15 Pro Max with titanium design, A17 Pro chip, and 48MP camera system.',
            'image_url' => 'https://picsum.photos/seed/iphone15/800/800',
            'details' => [
                'brand' => 'Apple',
                'model' => 'iPhone 15 Pro Max',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Chamkarmon',
                'sangkat' => 'Sangkat Toul Tompong',
                'address' => 'Street 155, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 3, 'value' => '8GB'],
                ['attribute_id' => 4, 'value' => '256GB'],
                ['attribute_id' => 2, 'value' => 'Natural Titanium'],
            ],
        ],
        [
            'name' => 'Samsung Galaxy S24 Ultra 512GB',
            'subcategory_id' => 1,
            'price' => 1099.00,
            'description' => 'Samsung flagship with S Pen, 200MP camera, Snapdragon 8 Gen 3 processor.',
            'image_url' => 'https://picsum.photos/seed/samsung24/800/800',
            'details' => [
                'brand' => 'Samsung',
                'model' => 'Galaxy S24 Ultra',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Toul Kork',
                'sangkat' => 'Sangkat Boeng Kak Ti Pir',
                'address' => 'Russian Boulevard, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 3, 'value' => '12GB'],
                ['attribute_id' => 4, 'value' => '512GB'],
                ['attribute_id' => 2, 'value' => 'Titanium Gray'],
            ],
        ],
        [
            'name' => 'MacBook Pro 14" M3 Pro',
            'subcategory_id' => 2,
            'price' => 2499.00,
            'description' => 'Apple MacBook Pro 14-inch with M3 Pro chip, 18GB RAM, 512GB SSD. Perfect for professionals.',
            'image_url' => 'https://picsum.photos/seed/macbookpro/800/800',
            'details' => [
                'brand' => 'Apple',
                'model' => 'MacBook Pro 14"',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Chamkarmon',
                'sangkat' => 'Sangkat Phsar Daeum Kor',
                'address' => 'Monivong Boulevard, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 3, 'value' => '18GB'],
                ['attribute_id' => 4, 'value' => '512GB SSD'],
                ['attribute_id' => 9, 'value' => 'Apple M3 Pro'],
                ['attribute_id' => 2, 'value' => 'Space Black'],
            ],
        ],
        [
            'name' => 'Dell XPS 15 9530',
            'subcategory_id' => 2,
            'price' => 1899.00,
            'description' => 'Dell XPS 15 with Intel Core i7-13700H, 16GB RAM, 512GB SSD, OLED display.',
            'image_url' => 'https://picsum.photos/seed/dellxps/800/800',
            'details' => [
                'brand' => 'Dell',
                'model' => 'XPS 15 9530',
                'condition' => 'used',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Daun Penh',
                'sangkat' => 'Sangkat Phsar Thmei Ti Pir',
                'address' => 'Street 215, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 3, 'value' => '16GB'],
                ['attribute_id' => 4, 'value' => '512GB SSD'],
                ['attribute_id' => 9, 'value' => 'Intel Core i7-13700H'],
                ['attribute_id' => 2, 'value' => 'Platinum Silver'],
            ],
        ],
        [
            'name' => 'iPad Air M2 11-inch',
            'subcategory_id' => 3,
            'price' => 799.00,
            'description' => 'Apple iPad Air with M2 chip, 11-inch Liquid Retina display, 128GB storage.',
            'image_url' => 'https://picsum.photos/seed/ipadair/800/800',
            'details' => [
                'brand' => 'Apple',
                'model' => 'iPad Air M2',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Chamkarmon',
                'sangkat' => 'Sangkat Toul Tompong Ti Pir',
                'address' => 'Street 155, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 3, 'value' => '8GB'],
                ['attribute_id' => 4, 'value' => '128GB'],
                ['attribute_id' => 2, 'value' => 'Space Gray'],
                ['attribute_id' => 8, 'value' => '11-inch Liquid Retina'],
            ],
        ],
        [
            'name' => 'Sony WH-1000XM5 Headphones',
            'subcategory_id' => 4,
            'price' => 349.00,
            'description' => 'Industry-leading noise cancelling headphones with 30-hour battery life and multipoint connection.',
            'image_url' => 'https://picsum.photos/seed/sonyxm5/800/800',
            'details' => [
                'brand' => 'Sony',
                'model' => 'WH-1000XM5',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Mean Chey',
                'sangkat' => 'Sangkat Boeng Tumpun',
                'address' => 'Street 271, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 2, 'value' => 'Black'],
                ['attribute_id' => 7, 'value' => '250g'],
            ],
        ],
        [
            'name' => 'AirPods Pro 2nd Gen USB-C',
            'subcategory_id' => 4,
            'price' => 249.00,
            'description' => 'Apple AirPods Pro with adaptive audio, personalized spatial audio, and USB-C charging.',
            'image_url' => 'https://picsum.photos/seed/airpodspro/800/800',
            'details' => [
                'brand' => 'Apple',
                'model' => 'AirPods Pro 2',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Chamkarmon',
                'sangkat' => 'Sangkat Toul Tompong',
                'address' => 'Street 155, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 2, 'value' => 'White'],
            ],
        ],
        [
            'name' => 'Nike Air Max 270',
            'subcategory_id' => 8,
            'price' => 159.00,
            'description' => 'Nike Air Max 270 with Max Air unit for unrivaled cushioning. Brand new in box.',
            'image_url' => 'https://picsum.photos/seed/nikeairmax/800/800',
            'details' => [
                'brand' => 'Nike',
                'model' => 'Air Max 270',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Daun Penh',
                'sangkat' => 'Sangkat Phsar Thmei Ti Bei',
                'address' => 'Sihanouk Boulevard, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 2, 'value' => 'Black/White'],
                ['attribute_id' => 5, 'value' => 'US 10'],
                ['attribute_id' => 6, 'value' => 'Mesh/Synthetic'],
            ],
        ],
        [
            'name' => 'Adidas Ultraboost Light',
            'subcategory_id' => 8,
            'price' => 189.00,
            'description' => 'Adidas Ultraboost Light running shoes with BOOST midsole and Continental rubber outsole.',
            'image_url' => 'https://picsum.photos/seed/adidasboost/800/800',
            'details' => [
                'brand' => 'Adidas',
                'model' => 'Ultraboost Light',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Chamkarmon',
                'sangkat' => 'Sangkat Phsar Daeum Kor',
                'address' => 'Street 19, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 2, 'value' => 'Core Black'],
                ['attribute_id' => 5, 'value' => 'US 9'],
                ['attribute_id' => 6, 'value' => 'Primeknit+'],
            ],
        ],
        [
            'name' => 'IKEA KALLAX Shelf Unit 4x4',
            'subcategory_id' => 9,
            'price' => 129.00,
            'description' => 'IKEA KALLAX shelf unit, white, 4x4 cubes. Great for storage and room divider.',
            'image_url' => 'https://picsum.photos/seed/ikeashelf/800/800',
            'details' => [
                'brand' => 'IKEA',
                'model' => 'KALLAX',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Sen Sok',
                'sangkat' => 'Sangkat Phnom Penh Thmei',
                'address' => 'Aeon Mall Sen Sok, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 5, 'value' => '147x147 cm'],
                ['attribute_id' => 2, 'value' => 'White'],
                ['attribute_id' => 6, 'value' => 'Particleboard'],
            ],
        ],
        [
            'name' => 'Philips Hue Starter Kit',
            'subcategory_id' => 10,
            'price' => 199.00,
            'description' => 'Philips Hue White and Color Ambiance starter kit with 3 bulbs and bridge. Smart lighting.',
            'image_url' => 'https://picsum.photos/seed/philipshue/800/800',
            'details' => [
                'brand' => 'LG',
                'model' => 'Hue Starter Kit',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Chamkarmon',
                'sangkat' => 'Sangkat Toul Tompong Ti Bei',
                'address' => 'Street 110, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 2, 'value' => 'White/Color'],
            ],
        ],
        [
            'name' => 'Vitamix E310 Explorian Blender',
            'subcategory_id' => 11,
            'price' => 349.00,
            'description' => 'Vitamix E310 Explorian blender, 10-speed, 48oz container. Professional-grade blending.',
            'image_url' => 'https://picsum.photos/seed/vitamix/800/800',
            'details' => [
                'brand' => 'Vitamix',
                'model' => 'E310 Explorian',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Toul Kork',
                'sangkat' => 'Sangkat Phnom Penh Thmei',
                'address' => 'Street 173, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 2, 'value' => 'Slate Grey'],
                ['attribute_id' => 7, 'value' => '5.7 kg'],
                ['attribute_id' => 5, 'value' => '48 oz'],
            ],
        ],
        [
            'name' => 'Bowflex SelectTech 552 Dumbbells',
            'subcategory_id' => 12,
            'price' => 429.00,
            'description' => 'Bowflex SelectTech 552 adjustable dumbbells, replace 15 sets of weights. 5-52.5 lbs each.',
            'image_url' => 'https://picsum.photos/seed/bowflex/800/800',
            'details' => [
                'brand' => 'Bowflex',
                'model' => 'SelectTech 552',
                'condition' => 'new',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Mean Chey',
                'sangkat' => 'Sangkat Stung Meanchey Ti Muoy',
                'address' => 'National Road 2, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 7, 'value' => '12.8 kg each'],
                ['attribute_id' => 5, 'value' => '5-52.5 lbs'],
            ],
        ],
        [
            'name' => 'Trek Marlin 7 Mountain Bike',
            'subcategory_id' => 13,
            'price' => 1149.00,
            'description' => 'Trek Marlin 7 hardtail mountain bike, 29" wheels, RockShox fork, Shimano Deore drivetrain.',
            'image_url' => 'https://picsum.photos/seed/trekbike/800/800',
            'details' => [
                'brand' => 'Trek',
                'model' => 'Marlin 7',
                'condition' => 'used',
                'province' => 'Phnom Penh',
                'khan' => 'Khan Sen Sok',
                'sangkat' => 'Sangkat Khmuonh',
                'address' => 'Street 1986, Phnom Penh',
            ],
            'attributes' => [
                ['attribute_id' => 2, 'value' => 'Lithium Grey'],
                ['attribute_id' => 5, 'value' => '18.5" frame'],
                ['attribute_id' => 7, 'value' => '13.4 kg'],
            ],
        ],
        [
            'name' => 'Coleman Sundome 4-Person Tent',
            'subcategory_id' => 14,
            'price' => 89.00,
            'description' => 'Coleman Sundome 4-person tent, WeatherTec system, easy setup in 10 minutes.',
            'image_url' => 'https://picsum.photos/seed/colemantent/800/800',
            'details' => [
                'brand' => 'Coleman',
                'model' => 'Sundome 4',
                'condition' => 'new',
                'province' => 'Siem Reap',
                'khan' => 'Siem Reap City',
                'sangkat' => 'Sangkat Svay Dangkum',
                'address' => 'Pub Street, Siem Reap',
            ],
            'attributes' => [
                ['attribute_id' => 5, 'value' => '231 x 213 cm'],
                ['attribute_id' => 2, 'value' => 'Green'],
                ['attribute_id' => 7, 'value' => '4.5 kg'],
                ['attribute_id' => 6, 'value' => 'Polyester'],
            ],
        ],
    ];

    public function run(): void
    {
        $disk = Storage::disk('public');

        foreach ($this->products as $index => $data) {
            $product = Product::create([
                'user_id' => $this->userId,
                'subcategory_id' => $data['subcategory_id'],
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'price' => $data['price'],
                'is_active' => true,
                'status' => 'approved',
            ]);

            ProductDetail::create([
                'product_id' => $product->id,
                ...$data['details'],
            ]);

            foreach ($data['attributes'] as $attr) {
                ProductAttribute::create([
                    'product_id' => $product->id,
                    'attribute_id' => $attr['attribute_id'],
                    'value' => $attr['value'],
                ]);
            }

            $dir = 'products/'.$product->id;
            $filename = 'main.png';
            $path = $dir.'/'.$filename;

            try {
                $imageContent = Http::timeout(15)->get($data['image_url'])->body();
                $disk->put($path, $imageContent);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            } catch (\Exception $e) {
                $this->command?->warn("Failed to download image for: {$data['name']} - {$e->getMessage()}");
            }
        }
    }
}
