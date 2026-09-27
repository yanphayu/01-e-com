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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DemoTimelineSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $this->backdateUsers($now);
        $this->backdateProducts($now);
        $this->backdateComments($now);
        $this->backdateConversations($now);
        $this->backdateReports($now);
        $this->alignNotifications();
    }

    private function backdateUsers(Carbon $now): void
    {
        $users = User::query()->orderBy('id')->get();

        foreach ($users as $index => $user) {
            $daysAgo = 88 - ($index * round(78 / max($users->count() - 1, 1)));
            $createdAt = $now->copy()->subDays(max($daysAgo, 1))->subHours($this->jitter($user->id, 20));

            $user->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays($this->jitter($user->id + 1, 4)),
            ])->save();

            Profile::query()
                ->where('user_id', $user->id)
                ->update([
                    'created_at' => $createdAt->copy()->addMinutes(5),
                    'updated_at' => $createdAt->copy()->addDays($this->jitter($user->id + 2, 6)),
                ]);
        }
    }

    private function backdateProducts(Carbon $now): void
    {
        $products = Product::query()->orderBy('id')->get();

        foreach ($products as $index => $product) {
            $daysAgo = 66 - ($index * round(60 / max($products->count() - 1, 1)));
            $createdAt = $now->copy()->subDays(max($daysAgo, 2))->subHours($this->jitter($product->id, 18));

            $product->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays($this->jitter($product->id + 5, 3)),
            ])->save();
        }
    }

    private function backdateComments(Carbon $now): void
    {
        $comments = Comment::query()
            ->whereNull('parent_id')
            ->orderBy('id')
            ->get();

        foreach ($comments as $index => $comment) {
            $daysAgo = 26 - ($index * round(24 / max($comments->count() - 1, 1)));
            $createdAt = $now->copy()->subDays(max($daysAgo, 1))->subHours($this->jitter($comment->id, 22));

            $comment->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

            Comment::query()
                ->where('parent_id', $comment->id)
                ->update([
                    'created_at' => $createdAt->copy()->addHours(2 + $this->jitter($comment->id, 6)),
                    'updated_at' => $createdAt->copy()->addHours(2 + $this->jitter($comment->id, 6)),
                ]);
        }
    }

    private function backdateConversations(Carbon $now): void
    {
        $conversations = Conversation::query()->orderBy('id')->get();

        foreach ($conversations as $index => $conversation) {
            $daysAgo = 12 - ($index * round(9 / max($conversations->count() - 1, 1)));
            $startedAt = $now->copy()->subDays(max($daysAgo, 1))->subHours($this->jitter($conversation->id, 16));

            $messages = Message::query()
                ->where('conversation_id', $conversation->id)
                ->orderBy('id')
                ->get();

            $messages->each(function (Message $message, int $position) use ($startedAt): void {
                $createdAt = $startedAt->copy()
                    ->addMinutes($position * (17 + $this->jitter($message->id, 40)));

                $message->forceFill([
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'edited_at' => null,
                ])->save();
            });

            $conversation->forceFill([
                'created_at' => $startedAt,
                'updated_at' => $startedAt,
                'last_message_at' => $messages->last()?->created_at ?? $startedAt,
            ])->save();
        }
    }

    private function backdateReports(Carbon $now): void
    {
        $reports = Report::query()->orderBy('id')->get();

        foreach ($reports as $index => $report) {
            $daysAgo = 11 - ($index * round(9 / max($reports->count() - 1, 1)));
            $createdAt = $now->copy()->subDays(max($daysAgo, 1))->subHours($this->jitter($report->id, 14));

            $report->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays($report->status === 'pending' ? 0 : 1),
            ])->save();
        }
    }

    private function alignNotifications(): void
    {
        $comments = Comment::query()->pluck('created_at', 'id');

        DB::table('notifications')
            ->where('type', CommentCreated::class)
            ->orderBy('id')
            ->get()
            ->each(function (object $notification) use ($comments): void {
                $commentId = json_decode($notification->data, true)['id'] ?? null;
                $createdAt = $comments[$commentId] ?? $notification->created_at;

                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update(['created_at' => $createdAt, 'updated_at' => $createdAt]);
            });

        $reports = Report::query()->pluck('created_at', 'id');

        DB::table('notifications')
            ->where('type', ProductReported::class)
            ->orderBy('id')
            ->get()
            ->each(function (object $notification) use ($reports): void {
                $reportId = json_decode($notification->data, true)['id'] ?? null;
                $createdAt = $reports[$reportId] ?? $notification->created_at;

                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update(['created_at' => $createdAt, 'updated_at' => $createdAt]);
            });
    }

    private function jitter(int $seed, int $spread): int
    {
        return abs(crc32('demo-timeline-'.$seed)) % max($spread, 1);
    }
}
