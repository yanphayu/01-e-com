<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\ProductImage;
use App\Models\ProductModel;
use App\Models\ProductPhone;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserProductSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->getUsers();
        $phoneNumbers = [
            '0882376158',
            '092345678',
            '012345678',
            '070987654',
            '089123456',
            '011234567',
            '093456789',
            '069876543',
            '086789012',
            '015678901',
        ];

        foreach ($users as $index => $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => '12345678',
                'email_verified_at' => now(),
            ]);

            $profile = Profile::create([
                'user_id' => $user->id,
                'phone' => $phoneNumbers[$index],
                'birth_date' => $userData['birth_date'],
            ]);

            Address::create([
                'profile_id' => $profile->id,
                'address' => $userData['address'],
                'latitude' => $userData['lat'],
                'longitude' => $userData['lng'],
            ]);

            $products = $this->getProductsForUser($index);
            foreach ($products as $productData) {
                $this->createProduct($user->id, $productData);
            }
        }
    }

    private function createProduct(int $userId, array $data): void
    {
        $product = Product::create([
            'user_id' => $userId,
            'subcategory_id' => $data['subcategory_id'],
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(5),
            'description' => $data['description'],
            'price' => $data['price'],
            'is_active' => true,
            'status' => 'approved',
        ]);

        $detailData = [
            'product_id' => $product->id,
            'condition' => $data['condition'] ?? 'new',
            'province' => $data['province'],
            'khan' => $data['khan'] ?? null,
            'sangkat' => $data['sangkat'] ?? null,
            'address' => $data['address'] ?? null,
            'latitude' => $data['lat'],
            'longitude' => $data['lng'],
        ];

        if (! empty($data['brand'])) {
            $brand = Brand::firstOrCreate(
                ['name' => $data['brand'], 'subcategory_id' => $data['subcategory_id']],
                ['name' => $data['brand'], 'subcategory_id' => $data['subcategory_id']]
            );
            $detailData['brand_id'] = $brand->id;

            if (! empty($data['model'])) {
                $model = ProductModel::firstOrCreate(
                    ['brand_id' => $brand->id, 'name' => $data['model']],
                    ['brand_id' => $brand->id, 'name' => $data['model']]
                );
                $detailData['model_id'] = $model->id;
            }
        }

        ProductDetail::create($detailData);

        if (! empty($data['phones'])) {
            foreach ($data['phones'] as $i => $phone) {
                ProductPhone::create([
                    'product_id' => $product->id,
                    'phone' => $phone,
                    'is_primary' => $i === 0,
                ]);
            }
        }

        $imageSeed = Str::slug($data['name']);
        $imageUrl = 'https://picsum.photos/seed/'.$imageSeed.'/800/800';
        $dir = 'products/'.$product->id;
        $path = $dir.'/main.jpg';

        try {
            $imageContent = Http::timeout(10)->get($imageUrl)->body();
            Storage::disk('public')->put($path, $imageContent);
            ProductImage::create([
                'product_id' => $product->id,
                'image' => $path,
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        } catch (\Exception $e) {
            if (! Storage::disk('public')->exists('products/default.jpg')) {
                $this->writeDefaultPlaceholder();
            }
            ProductImage::create([
                'product_id' => $product->id,
                'image' => 'products/default.jpg',
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }
    }

    private function writeDefaultPlaceholder(): void
    {
        $path = storage_path('app/public/products/default.jpg');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $image = imagecreatetruecolor(800, 800);
        for ($y = 0; $y < 800; $y++) {
            $color = imagecolorallocate($image, 235 - ($y / 800) * 20, 240 - ($y / 800) * 15, 245 - ($y / 800) * 10);
            imageline($image, 0, $y, 800, $y, $color);
        }
        $dim = imagecolorallocate($image, 160, 180, 205);
        imagefilledellipse($image, 400, 400, 496, 496, $dim);
        $accent = imagecolorallocate($image, 70, 130, 230);
        imagearc($image, 400, 400, 496, 496, 0, 360, $accent);
        imagestring($image, 5, 370, 390, 'Trinity', imagecolorallocate($image, 255, 255, 255));
        imagejpeg($image, $path, 85);
        imagedestroy($image);
    }

    private function getUsers(): array
    {
        return [
            [
                'name' => 'Sophea Chan',
                'email' => 'sophea@example.com',
                'birth_date' => '1995-03-15',
                'address' => 'Street 214, Sangkat Boeng Keng Kang Ti Muoy, Khan Chamkarmon, Phnom Penh',
                'lat' => 11.5564,
                'lng' => 104.9282,
            ],
            [
                'name' => 'Dara Lim',
                'email' => 'dara@example.com',
                'birth_date' => '1990-07-22',
                'address' => 'Street 105, Sangkat Toul Tompong, Khan Chamkarmon, Phnom Penh',
                'lat' => 11.5498,
                'lng' => 104.9178,
            ],
            [
                'name' => 'Bopha Keo',
                'email' => 'bopha@example.com',
                'birth_date' => '1992-11-08',
                'address' => 'Street 173, Sangkat Boeng Kak Ti Pir, Khan Toul Kork, Phnom Penh',
                'lat' => 11.5678,
                'lng' => 104.9032,
            ],
            [
                'name' => 'Rotha Sean',
                'email' => 'rotha@example.com',
                'birth_date' => '1988-01-30',
                'address' => 'Street 371, Sangkat Boeng Tumpun, Khan Mean Chey, Phnom Penh',
                'lat' => 11.5352,
                'lng' => 104.9488,
            ],
            [
                'name' => 'Chantrea Hoeun',
                'email' => 'chantrea@example.com',
                'birth_date' => '1993-06-12',
                'address' => 'Street 289, Sangkat Phnom Penh Thmei, Khan Sen Sok, Phnom Penh',
                'lat' => 11.5842,
                'lng' => 104.8892,
            ],
            [
                'name' => 'Kosal Phan',
                'email' => 'kosal@example.com',
                'birth_date' => '1991-09-25',
                'address' => 'Street 24, Sangkat Phsar Daeum Kor, Khan Chamkarmon, Phnom Penh',
                'lat' => 11.5612,
                'lng' => 104.9258,
            ],
            [
                'name' => 'Chhaya Nhem',
                'email' => 'chhaya@example.com',
                'birth_date' => '1994-04-18',
                'address' => 'Street 155, Sangkat Stung Meanchey Ti Muoy, Khan Mean Chey, Phnom Penh',
                'lat' => 11.5412,
                'lng' => 104.9438,
            ],
            [
                'name' => 'Visal Khoeun',
                'email' => 'visal@example.com',
                'birth_date' => '1989-12-03',
                'address' => 'Street 110, Sangkat Toul Tompong Ti Bei, Khan Chamkarmon, Phnom Penh',
                'lat' => 11.5478,
                'lng' => 104.9158,
            ],
            [
                'name' => 'Srey Leak Heng',
                'email' => 'srey@example.com',
                'birth_date' => '1996-08-20',
                'address' => 'Street 19, Sangkat Phsar Thmei Ti Bei, Khan Daun Penh, Phnom Penh',
                'lat' => 11.5648,
                'lng' => 104.9198,
            ],
            [
                'name' => 'Veasna Ouk',
                'email' => 'veasna@example.com',
                'birth_date' => '1987-02-14',
                'address' => 'Street 380, Sangkat Khmuonh, Khan Sen Sok, Phnom Penh',
                'lat' => 11.5912,
                'lng' => 104.8942,
            ],
        ];
    }

    private function getProductsForUser(int $userIndex): array
    {
        $allProducts = [
            // User 0 - Sophea: Electronics (phones)
            [
                ['name' => 'iPhone 15 Pro Max 256GB', 'subcategory_id' => 1, 'price' => 1199, 'brand' => 'Apple', 'model' => 'iPhone 15 Pro Max', 'condition' => 'new', 'description' => 'Brand new sealed iPhone 15 Pro Max with titanium design, A17 Pro chip, and 48MP camera system. Comes with original Apple warranty.', 'phones' => ['+855882376158'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong', 'lat' => 11.5498, 'lng' => 104.9178],
                ['name' => 'Samsung Galaxy S24 Ultra 512GB', 'subcategory_id' => 1, 'price' => 1099, 'brand' => 'Samsung', 'model' => 'Galaxy S24 Ultra', 'condition' => 'new', 'description' => 'Samsung flagship with S Pen, 200MP camera, Snapdragon 8 Gen 3 processor. Titanium frame design.', 'phones' => ['+855882376158'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong', 'lat' => 11.5510, 'lng' => 104.9190],
                ['name' => 'Google Pixel 8 Pro 256GB', 'subcategory_id' => 1, 'price' => 849, 'brand' => 'Google', 'model' => 'Pixel 8 Pro', 'condition' => 'new', 'description' => 'Google Pixel 8 Pro with Tensor G3 chip, 50MP camera, 6.7-inch LTPO OLED display. AI-powered photography.', 'phones' => ['+855882376158'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong', 'lat' => 11.5505, 'lng' => 104.9185],
                ['name' => 'iPhone 14 128GB (Used)', 'subcategory_id' => 1, 'price' => 499, 'brand' => 'Apple', 'model' => 'iPhone 14', 'condition' => 'used', 'description' => 'iPhone 14 in excellent condition, 87% battery health. No scratches or dents. Includes charger and case.', 'phones' => ['+855882376158'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong', 'lat' => 11.5500, 'lng' => 104.9175],
                ['name' => 'Samsung Galaxy A54 5G', 'subcategory_id' => 1, 'price' => 349, 'brand' => 'Samsung', 'model' => 'Galaxy A54', 'condition' => 'new', 'description' => 'Samsung Galaxy A54 with 6.4-inch Super AMOLED, 50MP OIS camera, IP67 water resistance, 5000mAh battery.', 'phones' => ['+855882376158'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong', 'lat' => 11.5502, 'lng' => 104.9180],
                ['name' => 'Xiaomi 14 Ultra', 'subcategory_id' => 1, 'price' => 899, 'brand' => 'Xiaomi', 'model' => '14 Ultra', 'condition' => 'new', 'description' => 'Xiaomi 14 Ultra with Leica optics, 1-inch sensor, Snapdragon 8 Gen 3. Professional mobile photography.', 'phones' => ['+855882376158'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong', 'lat' => 11.5508, 'lng' => 104.9188],
                ['name' => 'OPPO Find X7 Ultra', 'subcategory_id' => 1, 'price' => 799, 'brand' => 'OPPO', 'model' => 'Find X7 Ultra', 'condition' => 'new', 'description' => 'OPPO Find X7 Ultra with dual periscope zoom cameras, Hasselblad collaboration. Premium build quality.', 'phones' => ['+855882376158'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong', 'lat' => 11.5495, 'lng' => 104.9172],
                ['name' => 'Vivo V30 Pro', 'subcategory_id' => 1, 'price' => 449, 'brand' => 'Vivo', 'model' => 'V30 Pro', 'condition' => 'new', 'description' => 'Vivo V30 Pro with ZEISS portrait camera, 50MP main sensor, curved 6.78-inch AMOLED display.', 'phones' => ['+855882376158'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong', 'lat' => 11.5501, 'lng' => 104.9179],
                ['name' => 'Samsung Galaxy Z Flip 5', 'subcategory_id' => 1, 'price' => 749, 'brand' => 'Samsung', 'model' => 'Galaxy Z Flip 5', 'condition' => 'new', 'description' => 'Samsung Galaxy Z Flip 5 foldable phone, 3.4-inch cover screen, Flex Mode. Compact and stylish.', 'phones' => ['+855882376158'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong', 'lat' => 11.5497, 'lng' => 104.9176],
                ['name' => 'iPhone 13 128GB (Used)', 'subcategory_id' => 1, 'price' => 379, 'brand' => 'Apple', 'model' => 'iPhone 13', 'condition' => 'used', 'description' => 'iPhone 13 in good condition, 90% battery health. Minor wear on edges. Factory reset and ready to use.', 'phones' => ['+855882376158'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong', 'lat' => 11.5503, 'lng' => 104.9183],
            ],
            // User 1 - Dara: Laptops & Tablets
            [
                ['name' => 'MacBook Pro 16" M3 Max', 'subcategory_id' => 2, 'price' => 3299, 'brand' => 'Apple', 'model' => 'MacBook Pro 16"', 'condition' => 'new', 'description' => 'MacBook Pro 16-inch with M3 Max chip, 36GB RAM, 1TB SSD. Liquid Retina XDR display.', 'phones' => ['+85592345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Pir', 'lat' => 11.5632, 'lng' => 104.9202],
                ['name' => 'Dell XPS 15 9530', 'subcategory_id' => 2, 'price' => 1899, 'brand' => 'Dell', 'model' => 'XPS 15', 'condition' => 'new', 'description' => 'Dell XPS 15 with Intel Core i7-13700H, 16GB RAM, 512GB SSD, OLED InfinityEdge display.', 'phones' => ['+85592345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Pir', 'lat' => 11.5635, 'lng' => 104.9205],
                ['name' => 'Lenovo ThinkPad X1 Carbon Gen 11', 'subcategory_id' => 2, 'price' => 1649, 'brand' => 'Lenovo', 'model' => 'ThinkPad X1 Carbon', 'condition' => 'new', 'description' => 'ThinkPad X1 Carbon Gen 11, Intel i7-1365U, 16GB RAM, 512GB SSD. Business ultrabook with best keyboard.', 'phones' => ['+85592345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Pir', 'lat' => 11.5628, 'lng' => 104.9198],
                ['name' => 'MacBook Air 15" M3', 'subcategory_id' => 2, 'price' => 1499, 'brand' => 'Apple', 'model' => 'MacBook Air 15"', 'condition' => 'new', 'description' => 'MacBook Air 15-inch with M3 chip, 16GB RAM, 512GB SSD. Fanless design, 18-hour battery life.', 'phones' => ['+85592345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Pir', 'lat' => 11.5630, 'lng' => 104.9200],
                ['name' => 'ASUS ROG Strix G16 (2024)', 'subcategory_id' => 2, 'price' => 1799, 'brand' => 'Asus', 'model' => 'ROG Strix G16', 'condition' => 'new', 'description' => 'Gaming laptop with Intel i9-14900HX, RTX 4070, 16GB DDR5, 1TB SSD, 165Hz display.', 'phones' => ['+85592345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Pir', 'lat' => 11.5638, 'lng' => 104.9208],
                ['name' => 'iPad Pro 13" M4', 'subcategory_id' => 3, 'price' => 1299, 'brand' => 'Apple', 'model' => 'iPad Pro 13"', 'condition' => 'new', 'description' => 'iPad Pro 13-inch with M4 chip, tandem OLED Ultra Retina XDR display, 256GB. Thinnest Apple device ever.', 'phones' => ['+85592345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Pir', 'lat' => 11.5625, 'lng' => 104.9195],
                ['name' => 'Samsung Galaxy Tab S9 Ultra', 'subcategory_id' => 3, 'price' => 999, 'brand' => 'Samsung', 'model' => 'Galaxy Tab S9 Ultra', 'condition' => 'new', 'description' => 'Samsung Galaxy Tab S9 Ultra with 14.6-inch Dynamic AMOLED 2X, S Pen included, Snapdragon 8 Gen 2.', 'phones' => ['+85592345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Pir', 'lat' => 11.5640, 'lng' => 104.9210],
                ['name' => 'MacBook Pro 14" M3 Pro (Used)', 'subcategory_id' => 2, 'price' => 1699, 'brand' => 'Apple', 'model' => 'MacBook Pro 14"', 'condition' => 'used', 'description' => 'MacBook Pro 14-inch M3 Pro, 18GB RAM, 512GB SSD. 95% battery cycle count. Excellent condition with original box.', 'phones' => ['+85592345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Pir', 'lat' => 11.5633, 'lng' => 104.9203],
                ['name' => 'HP Spectre x360 14', 'subcategory_id' => 2, 'price' => 1349, 'brand' => 'HP', 'model' => 'Spectre x360', 'condition' => 'new', 'description' => 'HP Spectre x360 14 convertible, Intel i7-1355U, 16GB RAM, 1TB SSD, 2.8K OLED touch display.', 'phones' => ['+85592345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Pir', 'lat' => 11.5626, 'lng' => 104.9196],
                ['name' => 'iPad Air M2 11-inch', 'subcategory_id' => 3, 'price' => 599, 'brand' => 'Apple', 'model' => 'iPad Air M2', 'condition' => 'new', 'description' => 'iPad Air with M2 chip, 11-inch Liquid Retina display, 128GB. Touch ID and Apple Pencil Pro support.', 'phones' => ['+85592345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Pir', 'lat' => 11.5637, 'lng' => 104.9207],
            ],
            // User 2 - Bopha: Fashion (shoes & clothes)
            [
                ['name' => 'Nike Air Jordan 1 Retro High OG', 'subcategory_id' => 8, 'price' => 189, 'brand' => 'Nike', 'model' => 'Air Jordan 1', 'condition' => 'new', 'description' => 'Air Jordan 1 Retro High OG in classic Chicago colorway. Premium leather upper, Air-Sole cushioning.', 'phones' => ['+85570987654'], 'province' => 'Phnom Penh', 'khan' => 'Khan Toul Kork', 'sangkat' => 'Sangkat Boeng Kak Ti Pir', 'lat' => 11.5678, 'lng' => 104.9032],
                ['name' => 'Adidas Samba OG', 'subcategory_id' => 8, 'price' => 119, 'brand' => 'Adidas', 'model' => 'Samba', 'condition' => 'new', 'description' => 'Adidas Samba OG iconic sneakers, leather upper, gum rubber outsole. Timeless streetwear staple.', 'phones' => ['+85570987654'], 'province' => 'Phnom Penh', 'khan' => 'Khan Toul Kork', 'sangkat' => 'Sangkat Boeng Kak Ti Pir', 'lat' => 11.5680, 'lng' => 104.9035],
                ['name' => 'Nike Air Max 90', 'subcategory_id' => 8, 'price' => 139, 'brand' => 'Nike', 'model' => 'Air Max 90', 'condition' => 'new', 'description' => 'Nike Air Max 90 with visible Max Air unit, waffle outsole, classic design. All-day comfort.', 'phones' => ['+85570987654'], 'province' => 'Phnom Penh', 'khan' => 'Khan Toul Kork', 'sangkat' => 'Sangkat Boeng Kak Ti Pir', 'lat' => 11.5682, 'lng' => 104.9038],
                ['name' => 'Levi\'s 501 Original Fit Jeans', 'subcategory_id' => 5, 'price' => 79, 'brand' => 'Levi\'s', 'model' => '501', 'condition' => 'new', 'description' => 'Levi\'s 501 Original Fit jeans, button fly, straight leg. 100% cotton denim. Classic American style.', 'phones' => ['+85570987654'], 'province' => 'Phnom Penh', 'khan' => 'Khan Toul Kork', 'sangkat' => 'Sangkat Boeng Kak Ti Pir', 'lat' => 11.5675, 'lng' => 104.9030],
                ['name' => 'Uniqlo Ultra Light Down Jacket', 'subcategory_id' => 5, 'price' => 69, 'brand' => 'Uniqlo', 'model' => 'Ultra Light Down', 'condition' => 'new', 'description' => 'Uniqlo Ultra Light Down jacket, packable, 640 fill power. Water repellent finish. Perfect for travel.', 'phones' => ['+85570987654'], 'province' => 'Phnom Penh', 'khan' => 'Khan Toul Kork', 'sangkat' => 'Sangkat Boeng Kak Ti Pir', 'lat' => 11.5685, 'lng' => 104.9040],
                ['name' => 'Zara Floral Summer Dress', 'subcategory_id' => 6, 'price' => 59, 'brand' => 'Zara', 'model' => 'Summer Collection', 'condition' => 'new', 'description' => 'Zara floral print summer dress, lightweight fabric, V-neckline. Perfect for warm weather.', 'phones' => ['+85570987654'], 'province' => 'Phnom Penh', 'khan' => 'Khan Toul Kork', 'sangkat' => 'Sangkat Boeng Kak Ti Pir', 'lat' => 11.5676, 'lng' => 104.9028],
                ['name' => 'H&M Oversized Blazer', 'subcategory_id' => 6, 'price' => 89, 'brand' => 'H&M', 'model' => 'Oversized Blazer', 'condition' => 'new', 'description' => 'H&M oversized blazer, single-breasted, notch lapels. Relaxed fit for casual or formal wear.', 'phones' => ['+85570987654'], 'province' => 'Phnom Penh', 'khan' => 'Khan Toul Kork', 'sangkat' => 'Sangkat Boeng Kak Ti Pir', 'lat' => 11.5674, 'lng' => 104.9025],
                ['name' => 'New Balance 990v6', 'subcategory_id' => 8, 'price' => 199, 'brand' => 'New Balance', 'model' => '990v6', 'condition' => 'new', 'description' => 'New Balance 990v6 Made in USA, premium suede and mesh, FuelCell midsole. Ultimate running shoe.', 'phones' => ['+85570987654'], 'province' => 'Phnom Penh', 'khan' => 'Khan Toul Kork', 'sangkat' => 'Sangkat Boeng Kak Ti Pir', 'lat' => 11.5688, 'lng' => 104.9042],
                ['name' => 'Puma Suede Classic XXI', 'subcategory_id' => 8, 'price' => 79, 'brand' => 'Puma', 'model' => 'Suede Classic', 'condition' => 'new', 'description' => 'Puma Suede Classic XXI sneakers, soft suede upper, rubber cupsole. Streetwear classic since 1968.', 'phones' => ['+85570987654'], 'province' => 'Phnom Penh', 'khan' => 'Khan Toul Kork', 'sangkat' => 'Sangkat Boeng Kak Ti Pir', 'lat' => 11.5679, 'lng' => 104.9033],
                ['name' => 'Nike Dri-FIT Running Shorts', 'subcategory_id' => 5, 'price' => 39, 'brand' => 'Nike', 'model' => 'Dri-FIT', 'condition' => 'new', 'description' => 'Nike Dri-FIT running shorts, built-in liner, side pockets, moisture-wicking fabric. 7-inch inseam.', 'phones' => ['+85570987654'], 'province' => 'Phnom Penh', 'khan' => 'Khan Toul Kork', 'sangkat' => 'Sangkat Boeng Kak Ti Pir', 'lat' => 11.5673, 'lng' => 104.9023],
            ],
            // User 3 - Rotha: Home & Garden
            [
                ['name' => 'IKEA MALM Bed Frame Queen', 'subcategory_id' => 9, 'price' => 299, 'brand' => 'IKEA', 'model' => 'MALM', 'condition' => 'new', 'description' => 'IKEA MALM bed frame, queen size, high bed with storage option. Clean modern design in white.', 'phones' => ['+85512345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Boeng Tumpun', 'lat' => 11.5352, 'lng' => 104.9488],
                ['name' => 'Xiaomi Robot Vacuum X10+', 'subcategory_id' => 11, 'price' => 549, 'brand' => 'Xiaomi', 'model' => 'Robot Vacuum X10+', 'condition' => 'new', 'description' => 'Xiaomi Robot Vacuum X10+ with auto-empty dock, LDS navigation, 4000Pa suction. Mops and vacuums.', 'phones' => ['+85512345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Boeng Tumpun', 'lat' => 11.5355, 'lng' => 104.9490],
                ['name' => 'Philips Hue Starter Kit (4 Bulbs)', 'subcategory_id' => 10, 'price' => 179, 'brand' => 'Philips', 'model' => 'Hue Starter Kit', 'condition' => 'new', 'description' => 'Philips Hue White and Color Ambiance starter kit, 4 A19 bulbs + Hue Bridge. 16 million colors.', 'phones' => ['+85512345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Boeng Tumpun', 'lat' => 11.5348, 'lng' => 104.9485],
                ['name' => 'IKEA KALLAX Shelf Unit 4x4', 'subcategory_id' => 9, 'price' => 129, 'brand' => 'IKEA', 'model' => 'KALLAX', 'condition' => 'new', 'description' => 'IKEA KALLAX shelf unit 4x4, white. Versatile storage and room divider. Fits inserts and baskets.', 'phones' => ['+85512345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Boeng Tumpun', 'lat' => 11.5360, 'lng' => 104.9495],
                ['name' => 'Vitamix E310 Explorian Blender', 'subcategory_id' => 11, 'price' => 349, 'brand' => 'Vitamix', 'model' => 'E310 Explorian', 'condition' => 'new', 'description' => 'Vitamix E310 Explorian blender, 10-speed, 48oz container. Professional-grade for smoothies and soups.', 'phones' => ['+85512345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Boeng Tumpun', 'lat' => 11.5345, 'lng' => 104.9482],
                ['name' => 'Dyson V15 Detect Vacuum', 'subcategory_id' => 11, 'price' => 699, 'brand' => 'Dyson', 'model' => 'V15 Detect', 'condition' => 'new', 'description' => 'Dyson V15 Detect cordless vacuum with laser dust detection, HEPA filtration, 60-minute runtime.', 'phones' => ['+85512345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Boeng Tumpun', 'lat' => 11.5358, 'lng' => 104.9492],
                ['name' => 'IKEA HEMNES Dresser 8-Drawer', 'subcategory_id' => 9, 'price' => 349, 'brand' => 'IKEA', 'model' => 'HEMNES', 'condition' => 'new', 'description' => 'IKEA HEMNES 8-drawer dresser, white stain. Traditional style with smooth-running drawers.', 'phones' => ['+85512345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Boeng Tumpun', 'lat' => 11.5350, 'lng' => 104.9487],
                ['name' => 'KitchenAid Artisan Stand Mixer', 'subcategory_id' => 11, 'price' => 449, 'brand' => 'KitchenAid', 'model' => 'Artisan', 'condition' => 'new', 'description' => 'KitchenAid Artisan 5-quart stand mixer, tilt-head design, 10 speeds. Includes flat beater, dough hook, wire whip.', 'phones' => ['+85512345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Boeng Tumpun', 'lat' => 11.5342, 'lng' => 104.9478],
                ['name' => 'IKEA POÄNG Armchair', 'subcategory_id' => 9, 'price' => 149, 'brand' => 'IKEA', 'model' => 'POÄNG', 'condition' => 'new', 'description' => 'IKEA POÄNG armchair, birch veneer frame, cushion. Classic Scandinavian design with bounce.', 'phones' => ['+85512345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Boeng Tumpun', 'lat' => 11.5353, 'lng' => 104.9489],
                ['name' => 'Dyson Pure Cool Air Purifier', 'subcategory_id' => 10, 'price' => 499, 'brand' => 'Dyson', 'model' => 'Pure Cool', 'condition' => 'new', 'description' => 'Dyson Pure Cool air purifier, HEPA filter, bladeless design. Real-time air quality monitoring.', 'phones' => ['+85512345678'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Boeng Tumpun', 'lat' => 11.5347, 'lng' => 104.9483],
            ],
            // User 4 - Chhaya: Sports & Outdoors
            [
                ['name' => 'Trek Marlin 7 Mountain Bike', 'subcategory_id' => 13, 'price' => 1149, 'brand' => 'Trek', 'model' => 'Marlin 7', 'condition' => 'used', 'description' => 'Trek Marlin 7 hardtail mountain bike, 29-inch wheels, RockShox Judy fork, Shimano Deore 1x10 drivetrain.', 'phones' => ['+85589123456'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Phnom Penh Thmei', 'lat' => 11.5842, 'lng' => 104.8892],
                ['name' => 'Bowflex SelectTech 552 Dumbbells', 'subcategory_id' => 12, 'price' => 429, 'brand' => 'Bowflex', 'model' => 'SelectTech 552', 'condition' => 'new', 'description' => 'Bowflex SelectTech 552 adjustable dumbbells, replaces 15 sets of weights. 5 to 52.5 lbs each.', 'phones' => ['+85589123456'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Phnom Penh Thmei', 'lat' => 11.5845, 'lng' => 104.8895],
                ['name' => 'Coleman Sundome 4-Person Tent', 'subcategory_id' => 14, 'price' => 89, 'brand' => 'Coleman', 'model' => 'Sundome 4', 'condition' => 'new', 'description' => 'Coleman Sundome 4-person tent, WeatherTec system, easy 10-minute setup. Great ventilation with large windows.', 'phones' => ['+85589123456'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Phnom Penh Thmei', 'lat' => 11.5838, 'lng' => 104.8888],
                ['name' => 'Garmin Forerunner 265 GPS Watch', 'subcategory_id' => 12, 'price' => 449, 'brand' => 'Garmin', 'model' => 'Forerunner 265', 'condition' => 'new', 'description' => 'Garmin Forerunner 265 AMOLED running watch, training readiness, sleep tracking, 13-day battery life.', 'phones' => ['+85589123456'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Phnom Penh Thmei', 'lat' => 11.5850, 'lng' => 104.8900],
                ['name' => 'Giant Escape 3 City Bike', 'subcategory_id' => 13, 'price' => 449, 'brand' => 'Giant', 'model' => 'Escape 3', 'condition' => 'new', 'description' => 'Giant Escape 3 lightweight city bike, ALUXX aluminum frame, flat handlebar, 21-speed Shimano drivetrain.', 'phones' => ['+85589123456'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Phnom Penh Thmei', 'lat' => 11.5835, 'lng' => 104.8885],
                ['name' => 'Osprey Atmos 65 Backpack', 'subcategory_id' => 14, 'price' => 299, 'brand' => 'Osprey', 'model' => 'Atmos 65', 'condition' => 'new', 'description' => 'Osprey Atmos 65L backpack, Anti-Gravity suspension, integrated raincover. Perfect for multi-day treks.', 'phones' => ['+85589123456'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Phnom Penh Thmei', 'lat' => 11.5855, 'lng' => 104.8905],
                ['name' => 'Rogue Echo Bike', 'subcategory_id' => 12, 'price' => 795, 'brand' => 'Rogue', 'model' => 'Echo Bike', 'condition' => 'new', 'description' => 'Rogue Echo Bike air bike, steel frame, belt drive. No maintenance required. Perfect for HIIT workouts.', 'phones' => ['+85589123456'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Phnom Penh Thmei', 'lat' => 11.5848, 'lng' => 104.8898],
                ['name' => 'North Face Thermoball Eco Jacket', 'subcategory_id' => 14, 'price' => 199, 'brand' => 'The North Face', 'model' => 'Thermoball Eco', 'condition' => 'new', 'description' => 'The North Face ThermoBall Eco jacket, recycled insulation, water-resistant, packable into inner pocket.', 'phones' => ['+85589123456'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Phnom Penh Thmei', 'lat' => 11.5830, 'lng' => 104.8880],
                ['name' => 'Specialized Allez Road Bike (Used)', 'subcategory_id' => 13, 'price' => 699, 'brand' => 'Specialized', 'model' => 'Allez', 'condition' => 'used', 'description' => 'Specialized Allez road bike, Shimano Claris 8-speed, A1 SL aluminum frame. Recently serviced.', 'phones' => ['+85589123456'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Phnom Penh Thmei', 'lat' => 11.5860, 'lng' => 104.8910],
                ['name' => 'Black Diamond Storm 400 Headlamp', 'subcategory_id' => 14, 'price' => 59, 'brand' => 'Black Diamond', 'model' => 'Storm 400', 'condition' => 'new', 'description' => 'Black Diamond Storm 400 headlamp, 400 lumens, waterproof IPX8, rechargeable. 3 modes + red night-vision.', 'phones' => ['+85589123456'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Phnom Penh Thmei', 'lat' => 11.5825, 'lng' => 104.8875],
            ],
            // User 5 - Kosal: Electronics (accessories)
            [
                ['name' => 'Sony WH-1000XM5 Headphones', 'subcategory_id' => 4, 'price' => 349, 'brand' => 'Sony', 'model' => 'WH-1000XM5', 'condition' => 'new', 'description' => 'Sony WH-1000XM5 noise-cancelling headphones, 30-hour battery, multipoint connection, LDAC Hi-Res audio.', 'phones' => ['+85511234567'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Phsar Daeum Kor', 'lat' => 11.5612, 'lng' => 104.9258],
                ['name' => 'Apple AirPods Pro 2nd Gen USB-C', 'subcategory_id' => 4, 'price' => 249, 'brand' => 'Apple', 'model' => 'AirPods Pro 2', 'condition' => 'new', 'description' => 'AirPods Pro 2nd gen with USB-C, adaptive audio, personalized spatial audio, up to 2x more noise cancellation.', 'phones' => ['+85511234567'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Phsar Daeum Kor', 'lat' => 11.5615, 'lng' => 104.9260],
                ['name' => 'Samsung T7 Shield 2TB SSD', 'subcategory_id' => 4, 'price' => 179, 'brand' => 'Samsung', 'model' => 'T7 Shield', 'condition' => 'new', 'description' => 'Samsung T7 Shield portable SSD 2TB, IP65 water/dust resistant, 1050MB/s read. USB 3.2 Gen 2.', 'phones' => ['+85511234567'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Phsar Daeum Kor', 'lat' => 11.5610, 'lng' => 104.9255],
                ['name' => 'Logitech MX Master 3S Mouse', 'subcategory_id' => 4, 'price' => 99, 'brand' => 'Logitech', 'model' => 'MX Master 3S', 'condition' => 'new', 'description' => 'Logitech MX Master 3S wireless mouse, 8000 DPI sensor, MagSpeed scroll wheel, USB-C quick charging.', 'phones' => ['+85511234567'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Phsar Daeum Kor', 'lat' => 11.5618, 'lng' => 104.9262],
                ['name' => 'Anker 737 Power Bank 24000mAh', 'subcategory_id' => 4, 'price' => 109, 'brand' => 'Anker', 'model' => '737 PowerCore', 'condition' => 'new', 'description' => 'Anker 737 power bank 24000mAh, 140W max output, bidirectional USB-C charging, smart digital display.', 'phones' => ['+85511234567'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Phsar Daeum Kor', 'lat' => 11.5608, 'lng' => 104.9252],
                ['name' => 'JBL Charge 5 Bluetooth Speaker', 'subcategory_id' => 4, 'price' => 149, 'brand' => 'JBL', 'model' => 'Charge 5', 'condition' => 'new', 'description' => 'JBL Charge 5 waterproof Bluetooth speaker, 20-hour battery, JBL PartyBoost. Built-in power bank.', 'phones' => ['+85511234567'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Phsar Daeum Kor', 'lat' => 11.5620, 'lng' => 104.9265],
                ['name' => 'Keychron Q1 Pro Mechanical Keyboard', 'subcategory_id' => 4, 'price' => 199, 'brand' => 'Keychron', 'model' => 'Q1 Pro', 'condition' => 'new', 'description' => 'Keychron Q1 Pro wireless mechanical keyboard, CNC aluminum body, hot-swappable, Bluetooth 5.1.', 'phones' => ['+85511234567'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Phsar Daeum Kor', 'lat' => 11.5605, 'lng' => 104.9250],
                ['name' => 'Apple Watch Ultra 2', 'subcategory_id' => 4, 'price' => 799, 'brand' => 'Apple', 'model' => 'Watch Ultra 2', 'condition' => 'new', 'description' => 'Apple Watch Ultra 2 with S9 chip, 49mm titanium case, precision dual-frequency GPS, 36-hour battery.', 'phones' => ['+85511234567'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Phsar Daeum Kor', 'lat' => 11.5613, 'lng' => 104.9257],
                ['name' => 'Anker Soundcore Liberty 4 NC', 'subcategory_id' => 4, 'price' => 99, 'brand' => 'Anker', 'model' => 'Liberty 4 NC', 'condition' => 'new', 'description' => 'Anker Soundcore Liberty 4 NC earbuds, adaptive ANC, LDAC Hi-Res, 10-hour battery (50h with case).', 'phones' => ['+85511234567'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Phsar Daeum Kor', 'lat' => 11.5616, 'lng' => 104.9261],
                ['name' => 'DJI Mini 4 Pro Drone', 'subcategory_id' => 4, 'price' => 759, 'brand' => 'DJI', 'model' => 'Mini 4 Pro', 'condition' => 'new', 'description' => 'DJI Mini 4 Pro drone, 4K/60fps HDR, omnidirectional obstacle sensing, 34-min flight time, under 249g.', 'phones' => ['+85511234567'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Phsar Daeum Kor', 'lat' => 11.5602, 'lng' => 104.9248],
            ],
            // User 6 - Chhaya: Beauty & Health
            [
                ['name' => 'CeraVe Moisturizing Cream 340g', 'subcategory_id' => 18, 'price' => 24, 'brand' => 'CeraVe', 'model' => 'Moisturizing Cream', 'condition' => 'new', 'description' => 'CeraVe Moisturizing Cream with ceramides and hyaluronic acid. 24-hour hydration for face and body.', 'phones' => ['+85569876543'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Stung Meanchey Ti Muoy', 'lat' => 11.5412, 'lng' => 104.9438],
                ['name' => 'The Ordinary Niacinamide 10% + Zinc 1%', 'subcategory_id' => 18, 'price' => 12, 'brand' => 'The Ordinary', 'model' => 'Niacinamide 10%', 'condition' => 'new', 'description' => 'The Ordinary Niacinamide 10% + Zinc 1% serum, reduces blemishes and congestion. 30ml bottle.', 'phones' => ['+85569876543'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Stung Meanchey Ti Muoy', 'lat' => 11.5415, 'lng' => 104.9440],
                ['name' => 'Maybelline Lash Sensational Mascara', 'subcategory_id' => 19, 'price' => 14, 'brand' => 'Maybelline', 'model' => 'Lash Sensational', 'condition' => 'new', 'description' => 'Maybelline Lash Sensational Sky High mascara, volumizing and lengthening, washable formula.', 'phones' => ['+85569876543'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Stung Meanchey Ti Muoy', 'lat' => 11.5410, 'lng' => 104.9435],
                ['name' => 'L\'Oreal Paris Revitalift Hyaluronic Acid Serum', 'subcategory_id' => 18, 'price' => 29, 'brand' => 'L\'Oreal', 'model' => 'Revitalift', 'condition' => 'new', 'description' => 'L\'Oreal Revitalift hyaluronic acid serum, plumps and hydrates skin. 30ml with pure hyaluronic acid.', 'phones' => ['+85569876543'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Stung Meanchey Ti Muoy', 'lat' => 11.5418, 'lng' => 104.9442],
                ['name' => 'Nivea Sun SPF 50+ Protect & Moisture', 'subcategory_id' => 18, 'price' => 16, 'brand' => 'Nivea', 'model' => 'Sun SPF 50+', 'condition' => 'new', 'description' => 'Nivea Sun UV Face Sunscreen SPF 50+, moisturizing, non-greasy. UVA/UVB protection, 50ml tube.', 'phones' => ['+85569876543'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Stung Meanchey Ti Muoy', 'lat' => 11.5405, 'lng' => 104.9432],
                ['name' => 'Vitamin C 1000mg Effervescent (10 tabs)', 'subcategory_id' => 20, 'price' => 8, 'brand' => 'Redoxon', 'model' => 'Vitamin C 1000', 'condition' => 'new', 'description' => 'Redoxon Vitamin C 1000mg effervescent tablets, immune support, orange flavor. 10 tablets per tube.', 'phones' => ['+85569876543'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Stung Meanchey Ti Muoy', 'lat' => 11.5420, 'lng' => 104.9445],
                ['name' => 'MAC Matte Lipstick (Ruby Woo)', 'subcategory_id' => 19, 'price' => 25, 'brand' => 'MAC', 'model' => 'Matte Lipstick', 'condition' => 'new', 'description' => 'MAC Matte Lipstick in Ruby Woo, iconic red shade, long-wearing matte finish. 3g bullet.', 'phones' => ['+85569876543'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Stung Meanchey Ti Muoy', 'lat' => 11.5408, 'lng' => 104.9433],
                ['name' => 'Centrum Multivitamin (30 tablets)', 'subcategory_id' => 20, 'price' => 18, 'brand' => 'Centrum', 'model' => 'Multivitamin', 'condition' => 'new', 'description' => 'Centrum Adults multivitamin, complete from A to Zinc. 30 tablets for daily nutritional support.', 'phones' => ['+85569876543'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Stung Meanchey Ti Muoy', 'lat' => 11.5422, 'lng' => 104.9448],
                ['name' => 'Innisfree Green Tea Seed Serum', 'subcategory_id' => 18, 'price' => 32, 'brand' => 'Innisfree', 'model' => 'Green Tea Seed', 'condition' => 'new', 'description' => 'Innisfree Green Tea Seed serum, dual Moisturizing technology, 80ml. Antioxidant-rich Korean skincare.', 'phones' => ['+85569876543'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Stung Meanchey Ti Muoy', 'lat' => 11.5413, 'lng' => 104.9439],
                ['name' => 'Maybelline Fit Me Foundation', 'subcategory_id' => 19, 'price' => 12, 'brand' => 'Maybelline', 'model' => 'Fit Me', 'condition' => 'new', 'description' => 'Maybelline Fit Me Matte + Poreless foundation, oil-free, natural coverage. 30ml, shade 220.', 'phones' => ['+85569876543'], 'province' => 'Phnom Penh', 'khan' => 'Khan Mean Chey', 'sangkat' => 'Sangkat Stung Meanchey Ti Muoy', 'lat' => 11.5416, 'lng' => 104.9441],
            ],
            // User 7 - Visal: Sports & Automotive
            [
                ['name' => 'Honda PCX 160 (Used 2023)', 'subcategory_id' => 22, 'price' => 3200, 'brand' => 'Honda', 'model' => 'PCX 160', 'condition' => 'used', 'description' => 'Honda PCX 160 scooter, 2023 model, 8000km. Smart key, ABS, Honda Selectable Torque Control.', 'phones' => ['+85586789012'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong Ti Bei', 'lat' => 11.5478, 'lng' => 104.9158],
                ['name' => 'Michelin Pilot Sport 4S Tires (Set of 4)', 'subcategory_id' => 21, 'price' => 680, 'brand' => 'Michelin', 'model' => 'Pilot Sport 4S', 'condition' => 'new', 'description' => 'Michelin Pilot Sport 4S performance tires, 225/45R17. Ultra-high performance summer tires.', 'phones' => ['+85586789012'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong Ti Bei', 'lat' => 11.5480, 'lng' => 104.9160],
                ['name' => 'DeWalt 20V MAX Drill/Driver Kit', 'subcategory_id' => 23, 'price' => 159, 'brand' => 'DeWalt', 'model' => '20V MAX', 'condition' => 'new', 'description' => 'DeWalt 20V MAX drill/driver kit, 2-speed gearbox, 15 clutch settings. Includes 2 batteries and charger.', 'phones' => ['+85586789012'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong Ti Bei', 'lat' => 11.5475, 'lng' => 104.9155],
                ['name' => 'Bosch UniversalDrill 18V-55', 'subcategory_id' => 23, 'price' => 129, 'brand' => 'Bosch', 'model' => 'UniversalDrill', 'condition' => 'new', 'description' => 'Bosch UniversalDrill 18V-55 cordless drill, 2-speed, 20Nm torque. includes 2x 1.5Ah batteries.', 'phones' => ['+85586789012'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong Ti Bei', 'lat' => 11.5482, 'lng' => 104.9162],
                ['name' => 'Garmin DriveSmart 71 LMT-S GPS', 'subcategory_id' => 22, 'price' => 199, 'brand' => 'Garmin', 'model' => 'DriveSmart 71', 'condition' => 'new', 'description' => 'Garmin DriveSmart 71 GPS navigator, 7-inch display, voice-activated, lifetime maps and traffic.', 'phones' => ['+85586789012'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong Ti Bei', 'lat' => 11.5476, 'lng' => 104.9156],
                ['name' => 'Stanley FatMax Jump Starter', 'subcategory_id' => 22, 'price' => 89, 'brand' => 'Stanley', 'model' => 'FatMax', 'condition' => 'new', 'description' => 'Stanley FatMax 1000A jump starter, 12V, USB power bank, LED flashlight. Starts up to 8L gas engines.', 'phones' => ['+85586789012'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong Ti Bei', 'lat' => 11.5483, 'lng' => 104.9163],
                ['name' => 'Recaro Profi SPG Racing Seat', 'subcategory_id' => 21, 'price' => 899, 'brand' => 'Recaro', 'model' => 'Profi SPG', 'condition' => 'new', 'description' => 'Recaro Profi SPG racing seat, fiberglass shell, FIA approved. Side mounts included.', 'phones' => ['+85586789012'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong Ti Bei', 'lat' => 11.5473, 'lng' => 104.9153],
                ['name' => 'Goodyear Eagle F1 Asymmetric 6 Tires', 'subcategory_id' => 21, 'price' => 520, 'brand' => 'Goodyear', 'model' => 'Eagle F1', 'condition' => 'new', 'description' => 'Goodyear Eagle F1 Asymmetric 6, 235/40R18, summer performance tires. Excellent dry and wet grip.', 'phones' => ['+85586789012'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong Ti Bei', 'lat' => 11.5477, 'lng' => 104.9157],
                ['name' => 'Milwaukee M18 FUEL Impact Driver', 'subcategory_id' => 23, 'price' => 179, 'brand' => 'Milwaukee', 'model' => 'M18 FUEL', 'condition' => 'new', 'description' => 'Milwaukee M18 FUEL 1/4 inch impact driver, 2000 in-lbs torque, ONE-KEY compatible. Bare tool only.', 'phones' => ['+85586789012'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong Ti Bei', 'lat' => 11.5485, 'lng' => 104.9165],
                ['name' => 'Shell Helix Ultra 5W-40 Engine Oil (4L)', 'subcategory_id' => 22, 'price' => 55, 'brand' => 'Shell', 'model' => 'Helix Ultra', 'condition' => 'new', 'description' => 'Shell Helix Ultra 5W-40 fully synthetic engine oil, 4 liters. Advanced full synthetic for engine protection.', 'phones' => ['+85586789012'], 'province' => 'Phnom Penh', 'khan' => 'Khan Chamkarmon', 'sangkat' => 'Sangkat Toul Tompong Ti Bei', 'lat' => 11.5479, 'lng' => 104.9159],
            ],
            // User 8 - Srey: Fashion (women)
            [
                ['name' => 'Zara Satin Midi Skirt', 'subcategory_id' => 6, 'price' => 49, 'brand' => 'Zara', 'model' => 'Satin Midi', 'condition' => 'new', 'description' => 'Zara satin midi skirt, high waist, side slit. Luxurious satin fabric, elegant drape.', 'phones' => ['+85515678901'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Bei', 'lat' => 11.5648, 'lng' => 104.9198],
                ['name' => 'H&M Cashmere Sweater', 'subcategory_id' => 6, 'price' => 79, 'brand' => 'H&M', 'model' => 'Cashmere Sweater', 'condition' => 'new', 'description' => 'H&M 100% cashmere sweater, round neckline, relaxed fit. Soft and warm premium quality.', 'phones' => ['+85515678901'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Bei', 'lat' => 11.5650, 'lng' => 104.9200],
                ['name' => 'Nike Air Force 1 Sage Low', 'subcategory_id' => 8, 'price' => 139, 'brand' => 'Nike', 'model' => 'Air Force 1 Sage', 'condition' => 'new', 'description' => 'Nike Air Force 1 Sage Low, platform sole, premium leather upper, classic AF1 design reimagined.', 'phones' => ['+85515678901'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Bei', 'lat' => 11.5645, 'lng' => 104.9195],
                ['name' => 'Forever 21 Floral Wrap Dress', 'subcategory_id' => 6, 'price' => 29, 'brand' => 'Forever 21', 'model' => 'Floral Wrap', 'condition' => 'new', 'description' => 'Forever 21 floral print wrap dress, V-neckline, tie waist. Lightweight and flowy.', 'phones' => ['+85515678901'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Bei', 'lat' => 11.5655, 'lng' => 104.9205],
                ['name' => 'Uniqlo UV Cut Wide Leg Pants', 'subcategory_id' => 6, 'price' => 39, 'brand' => 'Uniqlo', 'model' => 'UV Cut Pants', 'condition' => 'new', 'description' => 'Uniqlo UV Cut wide leg pants, high waist, UV protection UPF50+. Wrinkle-resistant fabric.', 'phones' => ['+85515678901'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Bei', 'lat' => 11.5642, 'lng' => 104.9192],
                ['name' => 'Adidas Ultraboost Light (Pink)', 'subcategory_id' => 8, 'price' => 189, 'brand' => 'Adidas', 'model' => 'Ultraboost Light', 'condition' => 'new', 'description' => 'Adidas Ultraboost Light running shoes in pink, BOOST midsole, Continental rubber outsole. Ultra responsive.', 'phones' => ['+85515678901'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Bei', 'lat' => 11.5660, 'lng' => 104.9210],
                ['name' => 'Cotton On Linen Blazer', 'subcategory_id' => 6, 'price' => 59, 'brand' => 'Cotton On', 'model' => 'Linen Blazer', 'condition' => 'new', 'description' => 'Cotton On linen-blend blazer, relaxed fit, single button. Perfect for office or casual wear.', 'phones' => ['+85515678901'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Bei', 'lat' => 11.5638, 'lng' => 104.9188],
                ['name' => 'ASOS Design Bodycon Mini Dress', 'subcategory_id' => 6, 'price' => 35, 'brand' => 'ASOS', 'model' => 'Bodycon Mini', 'condition' => 'new', 'description' => 'ASOS Design bodycon mini dress, ribbed knit, square neckline. Figure-hugging stretch fabric.', 'phones' => ['+85515678901'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Bei', 'lat' => 11.5652, 'lng' => 104.9202],
                ['name' => 'Vans Old Skool Sneakers', 'subcategory_id' => 8, 'price' => 69, 'brand' => 'Vans', 'model' => 'Old Skool', 'condition' => 'new', 'description' => 'Vans Old Skool classic sneakers, canvas and suede, signature side stripe. Vulcanized rubber sole.', 'phones' => ['+85515678901'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Bei', 'lat' => 11.5646, 'lng' => 104.9196],
                ['name' => 'Shein Knit Cardigan Set', 'subcategory_id' => 6, 'price' => 25, 'brand' => 'Shein', 'model' => 'Knit Cardigan', 'condition' => 'new', 'description' => 'Shein knit cardigan set with matching cami top, soft knit fabric, cropped fit. Two-piece set.', 'phones' => ['+85515678901'], 'province' => 'Phnom Penh', 'khan' => 'Khan Daun Penh', 'sangkat' => 'Sangkat Phsar Thmei Ti Bei', 'lat' => 11.5658, 'lng' => 104.9208],
            ],
            // User 9 - Veasna: Toys & Books & Media
            [
                ['name' => 'LEGO Star Wars Millennium Falcon 75375', 'subcategory_id' => 24, 'price' => 169, 'brand' => 'LEGO', 'model' => 'Star Wars', 'condition' => 'new', 'description' => 'LEGO Star Wars Millennium Falcon 75375, 921 pieces. Detailed interior, 4 minifigures including Han Solo.', 'phones' => ['+85538090123'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Khmuonh', 'lat' => 11.5912, 'lng' => 104.8942],
                ['name' => 'Nintendo Switch OLED Model', 'subcategory_id' => 17, 'price' => 349, 'brand' => 'Nintendo', 'model' => 'Switch OLED', 'condition' => 'new', 'description' => 'Nintendo Switch OLED model, 7-inch OLED screen, enhanced audio, 64GB internal storage, wide adjustable stand.', 'phones' => ['+85538090123'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Khmuonh', 'lat' => 11.5915, 'lng' => 104.8945],
                ['name' => 'Hot Wheels Ultimate Garage Playset', 'subcategory_id' => 24, 'price' => 89, 'brand' => 'Hot Wheels', 'model' => 'Ultimate Garage', 'condition' => 'new', 'description' => 'Hot Wheels Ultimate Garage, 3-level parking, spiral drop, motorized elevator. Fits 60+ cars.', 'phones' => ['+85538090123'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Khmuonh', 'lat' => 11.5910, 'lng' => 104.8940],
                ['name' => 'PlayStation 5 Slim Console', 'subcategory_id' => 17, 'price' => 449, 'brand' => 'Sony', 'model' => 'PlayStation 5 Slim', 'condition' => 'new', 'description' => 'Sony PlayStation 5 Slim Digital Edition, 1TB SSD, DualSense wireless controller, 4K gaming.', 'phones' => ['+85538090123'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Khmuonh', 'lat' => 11.5920, 'lng' => 104.8950],
                ['name' => 'The Architecture of Happiness - Alain de Botton', 'subcategory_id' => 15, 'price' => 14, 'brand' => null, 'model' => null, 'condition' => 'new', 'description' => 'The Architecture of Happiness by Alain de Botton. Explores how our surroundings shape our wellbeing. Paperback.', 'phones' => ['+85538090123'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Khmuonh', 'lat' => 11.5908, 'lng' => 104.8938],
                ['name' => 'Dune: Part Two (Blu-ray 4K)', 'subcategory_id' => 16, 'price' => 29, 'brand' => null, 'model' => null, 'condition' => 'new', 'description' => 'Dune: Part Two 4K Ultra HD Blu-ray. Directed by Denis Villeneuve. Includes bonus features and digital copy.', 'phones' => ['+85538090123'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Khmuonh', 'lat' => 11.5925, 'lng' => 104.8955],
                ['name' => 'LEGO Technic Bugatti Chiron 42083', 'subcategory_id' => 25, 'price' => 349, 'brand' => 'LEGO', 'model' => 'Technic Bugatti', 'condition' => 'new', 'description' => 'LEGO Technic Bugatti Chiron 42083, 3599 pieces. Working W16 engine, active rear wing, detailed cockpit.', 'phones' => ['+85538090123'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Khmuonh', 'lat' => 11.5905, 'lng' => 104.8935],
                ['name' => 'Atomic Habits - James Clear', 'subcategory_id' => 15, 'price' => 16, 'brand' => null, 'model' => null, 'condition' => 'new', 'description' => 'Atomic Habits by James Clear. An easy and proven way to build good habits and break bad ones. Paperback.', 'phones' => ['+85538090123'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Khmuonh', 'lat' => 11.5918, 'lng' => 104.8948],
                ['name' => 'Xbox Series X Console', 'subcategory_id' => 17, 'price' => 499, 'brand' => 'Microsoft', 'model' => 'Xbox Series X', 'condition' => 'new', 'description' => 'Microsoft Xbox Series X, 12 teraflops, 1TB SSD, 4K gaming at 120fps, Game Pass compatible.', 'phones' => ['+85538090123'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Khmuonh', 'lat' => 11.5922, 'lng' => 104.8952],
                ['name' => 'Tamiya RC Off-Road Buggy Kit', 'subcategory_id' => 26, 'price' => 129, 'brand' => 'Tamiya', 'model' => 'RC Buggy', 'condition' => 'new', 'description' => 'Tamiya RC off-road buggy kit, 1/10 scale, shaft-driven 4WD. Requires motor, ESC, servo, and radio.', 'phones' => ['+85538090123'], 'province' => 'Phnom Penh', 'khan' => 'Khan Sen Sok', 'sangkat' => 'Sangkat Khmuonh', 'lat' => 11.5902, 'lng' => 104.8932],
            ],
        ];

        return $allProducts[$userIndex] ?? [];
    }
}
