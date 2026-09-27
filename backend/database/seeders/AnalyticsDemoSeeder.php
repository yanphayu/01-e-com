<?php

namespace Database\Seeders;

use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AnalyticsDemoSeeder extends Seeder
{
    private const EMAIL_PREFIX = 'analytics-demo-';

    public function run(): void
    {
        if (User::query()->where('email', 'like', self::EMAIL_PREFIX.'%')->exists()) {
            $this->command?->info('Analytics demo data already exists.');

            return;
        }

        $subcategories = Subcategory::query()
            ->with('category:id,name')
            ->orderBy('id')
            ->get();

        if ($subcategories->isEmpty()) {
            throw new RuntimeException('Seed categories before running the analytics demo seeder.');
        }

        DB::transaction(function () use ($subcategories): void {
            $users = $this->seedUsers();
            $products = $this->seedProducts($users, $subcategories);
            $conversations = $this->seedConversations($users);

            $this->seedComments($users, $products);
            $this->seedReports($users, $products);
            $this->seedMessages($conversations, $products);
        }, 3);

        $this->command?->info('Analytics demo data seeded.');
    }

    private function seedUsers(): array
    {
        $firstNames = ['Sophea', 'Dara', 'Bopha', 'Rotha', 'Chantrea', 'Kosal', 'Chhaya', 'Vichea', 'Sreymom', 'Panha'];
        $lastNames = ['Chan', 'Lim', 'Keo', 'Sean', 'Hoeun', 'Phan', 'Nhem', 'Sok', 'Chea', 'Mom'];
        $password = Hash::make('password');
        $users = [];

        foreach (range(1, 40) as $index) {
            $position = $index - 1;
            $daysAgo = $position < 20
                ? 89 - $position
                : 69 - intdiv(($position - 20) * 69, 19);
            $createdAt = $this->timestamp($daysAgo, $position);
            $userId = DB::table('users')->insertGetId([
                'name' => $firstNames[$position % 10].' '.$lastNames[intdiv($position, 10)],
                'email' => sprintf('%suser%03d@example.test', self::EMAIL_PREFIX, $index),
                'password' => $password,
                'is_admin' => false,
                'email_verified_at' => $createdAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $profileId = DB::table('profiles')->insertGetId([
                'user_id' => $userId,
                'phone' => sprintf('01%08d', 10000000 + $index),
                'birth_date' => now()->subYears(22 + ($index % 18))->subMonths($index % 12)->toDateString(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            DB::table('addresses')->insert([
                'profile_id' => $profileId,
                'address' => (($index % 20) + 1).' Demo Street, Phnom Penh',
                'latitude' => 11.5564 + (($index % 10) * 0.0021),
                'longitude' => 104.9282 + (($index % 10) * 0.0018),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $users[] = [
                'id' => $userId,
                'created_at' => $createdAt,
            ];
        }

        return $users;
    }

    private function seedProducts(array $users, $subcategories): array
    {
        $adjectives = ['Premium', 'Compact', 'Classic', 'Modern', 'Essential', 'Refurbished'];
        $statuses = ['approved', 'approved', 'approved', 'approved', 'approved', 'approved', 'approved', 'pending', 'pending', 'rejected'];
        $provinces = ['Phnom Penh', 'Kandal', 'Siem Reap', 'Battambang', 'Kampong Cham'];
        $districts = ['Sen Sok', 'Khan Chamkarmon', 'Mean Chey', 'Toul Kork', 'Kampong Kiang'];
        $products = [];

        foreach (range(1, 120) as $index) {
            $position = $index - 1;
            $daysAgo = intdiv($position * 89, 119);
            $createdAt = $this->timestamp($daysAgo, $position);
            $subcategory = $subcategories[$position % $subcategories->count()];
            $status = $statuses[$position % count($statuses)];
            $productId = DB::table('products')->insertGetId([
                'user_id' => $users[$position % 20]['id'],
                'subcategory_id' => $subcategory->id,
                'name' => $adjectives[$position % count($adjectives)].' '.$subcategory->name.' Demo '.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'slug' => 'analytics-demo-product-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'description' => 'Demonstration listing for analytics and catalog testing.',
                'price' => 25 + (($position * 37) % 1975) + 0.99,
                'is_active' => $status === 'approved',
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            DB::table('product_details')->insert([
                'product_id' => $productId,
                'address' => (($position % 80) + 1).' Commerce Road, Phnom Penh',
                'province' => $provinces[$position % count($provinces)],
                'khan' => $districts[$position % count($districts)],
                'sangkat' => 'Sangkat '.($position % 12 + 1),
                'latitude' => 11.5564 + (($position % 10) * 0.0021),
                'longitude' => 104.9282 + (($position % 10) * 0.0018),
                'condition' => $position % 5 === 0 ? 'used' : 'new',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            DB::table('product_images')->insert([
                'product_id' => $productId,
                'image' => 'products/default.jpg',
                'is_primary' => true,
                'sort_order' => 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $products[] = [
                'id' => $productId,
                'created_at' => $createdAt,
            ];
        }

        return $products;
    }

    private function seedComments(array $users, array $products): void
    {
        $comments = [
            'Is the item still available?',
            'Can you share more photos?',
            'The price looks reasonable.',
            'Would you accept delivery?',
            'Is the warranty included?',
            'Can we arrange a viewing this week?',
            'What is the condition?',
            'Please contact me when ready.',
        ];

        foreach (range(1, 240) as $index) {
            $position = $index - 1;
            $daysAgo = intdiv($position * 89, 239);
            $createdAt = $this->timestamp($daysAgo, $position + 40);
            $product = $this->productForDate($products, $daysAgo, $position);

            DB::table('comments')->insert([
                'user_id' => $users[($position * 7) % 20]['id'],
                'product_id' => $product['id'],
                'body' => $comments[$position % count($comments)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    private function seedReports(array $users, array $products): void
    {
        $reasons = ['Suspected fraud', 'Duplicate listing', 'Incorrect information', 'Prohibited item', 'Spam content'];
        $statuses = ['pending', 'resolved', 'dismissed'];

        foreach (range(1, 42) as $index) {
            $position = $index - 1;
            $daysAgo = intdiv($position * 89, 41);
            $createdAt = $this->timestamp($daysAgo, $position + 300);
            $product = $this->productForDate($products, $daysAgo, $position + 3);

            DB::table('reports')->insert([
                'user_id' => $users[($position * 11) % 20]['id'],
                'product_id' => $product['id'],
                'reason' => $reasons[$position % count($reasons)],
                'details' => 'Demo report used to populate moderation analytics.',
                'status' => $statuses[$position % count($statuses)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    private function seedConversations(array $users): array
    {
        $pairs = [
            [0, 1], [0, 2], [0, 3], [0, 4], [0, 5], [0, 6],
            [0, 7], [0, 8], [0, 9], [0, 10], [0, 11], [0, 12],
            [1, 13], [1, 14], [1, 15], [1, 16], [1, 17], [1, 18],
        ];
        $conversations = [];

        foreach ($pairs as $position => [$first, $second]) {
            $daysAgo = 85 - intdiv($position * 75, count($pairs) - 1);
            $createdAt = $this->timestamp($daysAgo, $position + 400);
            $conversationId = DB::table('conversations')->insertGetId([
                'user1_id' => $users[$first]['id'],
                'user2_id' => $users[$second]['id'],
                'last_message_at' => null,
                'is_pinned' => $position % 6 === 0,
                'is_muted' => false,
                'is_archived' => false,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $conversations[] = [
                'id' => $conversationId,
                'user1_id' => $users[$first]['id'],
                'user2_id' => $users[$second]['id'],
                'created_days_ago' => $daysAgo,
            ];
        }

        return $conversations;
    }

    private function seedMessages(array $conversations, array $products): void
    {
        $messages = [
            'Hello, is this still available?',
            'Yes, it is available.',
            'Could you share the condition?',
            'It is in very good condition.',
            'What is the latest price?',
            'The current price is listed above.',
            'Can we meet this week?',
            'Sure, I can meet tomorrow.',
            'Thank you for the information.',
            'You are welcome.',
        ];

        foreach ($conversations as $conversationIndex => $conversation) {
            $messageCount = 10 + (($conversationIndex * 3) % 8) * 2;
            $lastMessageAt = null;

            foreach (range(0, $messageCount - 1) as $messageIndex) {
                $daysAgo = intdiv(
                    $conversation['created_days_ago'] * ($messageCount - 1 - $messageIndex),
                    $messageCount - 1,
                );
                $sequence = ($conversationIndex * 50) + $messageIndex;
                $createdAt = $this->timestamp($daysAgo, $sequence);
                $product = $this->productForDate($products, $daysAgo, $sequence);
                $senderId = $messageIndex % 2 === 0
                    ? $conversation['user1_id']
                    : $conversation['user2_id'];

                DB::table('messages')->insert([
                    'conversation_id' => $conversation['id'],
                    'user_id' => $senderId,
                    'product_id' => $product['id'],
                    'body' => $messages[($conversationIndex + $messageIndex) % count($messages)],
                    'read_at' => $messageIndex < $messageCount - 1 ? $createdAt : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $lastMessageAt = $createdAt;
            }

            DB::table('conversations')
                ->where('id', $conversation['id'])
                ->update([
                    'last_message_at' => $lastMessageAt,
                    'updated_at' => $lastMessageAt,
                ]);
        }
    }

    private function productForDate(array $products, int $daysAgo, int $sequence): array
    {
        $minimumIndex = (int) ceil($daysAgo / 89 * 119);
        $available = count($products) - $minimumIndex;

        return $products[$minimumIndex + (($sequence * 13) % $available)];
    }

    private function timestamp(int $daysAgo, int $sequence): string
    {
        if ($daysAgo === 0) {
            return now()->subMinutes($sequence % 240)->toDateTimeString();
        }

        return now()
            ->subDays($daysAgo)
            ->setTime(7 + ($sequence % 14), ($sequence * 11) % 60, ($sequence * 17) % 60)
            ->toDateTimeString();
    }
}
