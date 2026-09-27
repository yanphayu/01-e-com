<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use App\Notifications\CommentCreated;
use App\Notifications\ProductReported;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->seedUsers();
        $products = Product::query()
            ->where('status', 'approved')
            ->orderBy('id')
            ->get(['id', 'name', 'user_id']);

        if ($products->count() < 3) {
            $this->command?->warn('Fewer than three approved products found; demo content skipped.');

            return;
        }

        $this->seedComments($users, $products);
        $this->seedConversations($users, $products);
        $this->seedReports($users, $products);
    }

    /**
     * @return Collection<int, User>
     */
    private function seedUsers(): Collection
    {
        $disk = Storage::disk('public');
        $avatars = array_values(array_filter($disk->files('avatars')));
        $covers = array_values(array_filter($disk->files('covers')));

        $definitions = [
            ['name' => 'Sokha Lim', 'email' => 'sokha.lim@demo.test', 'phone' => '012 555 101', 'birth_date' => '1996-04-18'],
            ['name' => 'Dara Novak', 'email' => 'dara.novak@demo.test', 'phone' => '012 555 202', 'birth_date' => '1993-11-02'],
            ['name' => 'Bopha Chea', 'email' => 'bopha.chea@demo.test', 'phone' => '012 555 303', 'birth_date' => '1999-07-25'],
            ['name' => 'Vichea Rith', 'email' => 'vichea.rith@demo.test', 'phone' => '012 555 404', 'birth_date' => '1991-01-30'],
        ];

        $users = collect();

        foreach ($definitions as $index => $definition) {
            $user = User::firstOrNew(['email' => $definition['email']]);
            $user->fill([
                'name' => $definition['name'],
                'password' => 'password',
                'is_admin' => false,
                'email_verified_at' => now(),
            ]);
            $user->save();

            $profile = Profile::firstOrNew(['user_id' => $user->id]);
            $profile->fill([
                'phone' => $definition['phone'],
                'birth_date' => $definition['birth_date'],
                'facebook' => null,
                'instagram' => null,
            ]);

            if (isset($avatars[$index])) {
                $path = 'avatars/demo-'.$user->id.'.'.pathinfo($avatars[$index], PATHINFO_EXTENSION);
                $disk->copy($avatars[$index], $path);
                $profile->avatar = $path;
            }

            if (isset($covers[$index])) {
                $path = 'covers/demo-'.$user->id.'.'.pathinfo($covers[$index], PATHINFO_EXTENSION);
                $disk->copy($covers[$index], $path);
                $profile->cover_image = $path;
            }

            $profile->save();

            $users->push($user);
        }

        return $users;
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Product>  $products
     */
    private function seedComments(Collection $users, Collection $products): void
    {
        $script = [
            ['Is this still available?', 'Yes, it is ready to ship tomorrow.'],
            ['Could you send a photo of the actual item?', 'Sure, I added two more pictures for you.'],
            ['Do you deliver to province 12?', 'Yes, I can deliver anywhere in the city.'],
            ['Is the price negotiable?', 'I can do 95 for a quick pickup.'],
        ];

        $targets = $products->take(4);

        foreach ($targets as $offset => $product) {
            $commenter = $users[($offset + 1) % $users->count()];

            $comment = Comment::firstOrCreate([
                'product_id' => $product->id,
                'user_id' => $commenter->id,
                'body' => $script[$offset % count($script)][0],
            ]);

            $reply = Comment::firstOrCreate([
                'product_id' => $product->id,
                'user_id' => $product->user_id,
                'parent_id' => $comment->id,
                'body' => $script[$offset % count($script)][1],
            ]);

            $this->notifySeller($product, $commenter, $comment);
            $this->notifySeller($product, $product->user_id ? $users->firstWhere('id', $product->user_id) : $commenter, $reply, $comment);
        }
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Product>  $products
     */
    private function seedConversations(Collection $users, Collection $products): void
    {
        $product = $products->first();
        $pairs = [
            [$users[0]->id, $users[1]->id, $product, [
                [$users[1]->id, 'Hi, is the camera still available?'],
                [$users[0]->id, 'It is, and it comes with the original box.'],
                [$users[1]->id, 'Great. Can I pick it up this weekend?'],
                [$users[0]->id, 'Saturday morning works for me. I can send you a location pin.'],
            ]],
            [$users[2]->id, $users[3]->id, null, [
                [$users[3]->id, 'Do you deliver to Kandal province?'],
                [$users[2]->id, 'Yes, delivery is 3 dollars anywhere in the city.'],
                [$users[3]->id, 'Perfect, I will order tonight.'],
            ]],
        ];

        foreach ($pairs as [$user1Id, $user2Id, $linkedProduct, $messages]) {
            $conversation = Conversation::firstOrCreate([
                'user1_id' => $user1Id,
                'user2_id' => $user2Id,
            ]);

            foreach ($messages as $index => [$senderId, $body]) {
                $message = Message::firstOrCreate([
                    'conversation_id' => $conversation->id,
                    'user_id' => $senderId,
                    'body' => $body,
                ], [
                    'product_id' => $linkedProduct?->id,
                    'read_at' => $index < count($messages) - 1 ? now() : null,
                    'created_at' => now()->subMinutes(count($messages) - $index),
                    'updated_at' => now()->subMinutes(count($messages) - $index),
                ]);

                if ($linkedProduct && $senderId === $user1Id) {
                    $message->forceFill(['product_id' => $linkedProduct->id])->save();
                }
            }

            $conversation->forceFill(['last_message_at' => now()])->save();
        }
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Product>  $products
     */
    private function seedReports(Collection $users, Collection $products): void
    {
        $admin = User::query()->where('is_admin', true)->first();
        $definitions = [
            ['reason' => 'counterfeit', 'details' => 'The photos look copied from another listing.'],
            ['reason' => 'misleading', 'details' => 'The description says brand new but the seller admitted it is used.'],
            ['reason' => 'duplicate', 'details' => 'Same item and price listed twice by the same seller.'],
        ];

        foreach ($definitions as $index => $definition) {
            $product = $products->get($index % $products->count());
            $reporter = $users[($index + 2) % $users->count()];

            $report = Report::firstOrCreate([
                'user_id' => $reporter->id,
                'product_id' => $product->id,
                'reason' => $definition['reason'],
            ], [
                'details' => $definition['details'],
                'status' => 'pending',
            ]);

            if ($admin && ! $admin->notifications()
                ->where('type', ProductReported::class)
                ->where('data->id', $report->id)
                ->exists()) {
                $admin->notify(new ProductReported($report));
            }
        }
    }

    private function notifySeller(Product $product, ?User $actor, Comment $comment, ?Comment $subject = null): void
    {
        if (! $actor || $actor->id === $product->user_id) {
            return;
        }

        $seller = User::find($product->user_id);

        if (! $seller) {
            return;
        }

        $payload = [
            'id' => ($subject ?? $comment)->id,
            'type' => 'comment_created',
            'user' => [
                'id' => $actor->id,
                'name' => $actor->name,
                'avatar' => $actor->profile?->avatar,
            ],
            'product_id' => $product->id,
            'product_name' => $product->name,
            'body' => $comment->body,
            'created_at' => $comment->created_at->toISOString(),
        ];

        $exists = $seller->notifications()
            ->where('type', CommentCreated::class)
            ->where('data->id', $payload['id'])
            ->exists();

        if (! $exists) {
            $seller->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => CommentCreated::class,
                'data' => $payload,
            ]);
        }
    }
}
