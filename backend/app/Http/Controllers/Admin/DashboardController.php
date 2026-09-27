<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $data = $this->snapshot();

        $traffic = $data['traffic'];

        $sparks = [];
        $trends = [];

        foreach (['users', 'products', 'comments'] as $key) {
            $series = collect($traffic)->pluck($key)->all();
            $sparks[$key] = $this->sparkline($series);
            $trends[$key] = $this->trend($series);
        }

        $users = $this->series($traffic, 'users');
        $products = $this->series($traffic, 'products');
        $comments = $this->series($traffic, 'comments');
        $messages = $this->series($traffic, 'messages');

        $chartData = [
            'labels' => collect($traffic)
                ->map(fn (array $row) => Carbon::parse($row['date'])->format('M j'))
                ->values()
                ->all(),
            'users' => $users,
            'products' => $products,
            'comments' => $comments,
            'messages' => $messages,
        ];

        $chatChartData = [
            'labels' => collect($data['chat']['messages_by_day'])
                ->map(fn (array $day) => Carbon::parse($day['date'])->format('D'))
                ->values()
                ->all(),
            'values' => collect($data['chat']['messages_by_day'])->pluck('count')->values()->all(),
        ];

        $totals = [
            'users' => array_sum($users),
            'products' => array_sum($products),
            'comments' => array_sum($comments),
            'messages' => array_sum($messages),
        ];

        return view('admin.dashboard', compact('data', 'sparks', 'trends', 'chartData', 'chatChartData', 'totals'));
    }

    public function analytics(Request $request): View
    {
        $range = in_array($request->integer('range'), [7, 30, 90], true) ? $request->integer('range') : 30;
        $data = $this->snapshot($range);
        $traffic = $data['traffic'];
        $activityLabels = collect($traffic)->map(
            fn (array $day): string => Carbon::parse($day['date'])->format('M j'),
        )->all();

        $productStatusCounts = Product::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $reportStatusCounts = Report::query()
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $categoryListings = Product::query()
            ->join('subcategories', 'subcategories.id', '=', 'products.subcategory_id')
            ->join('categories', 'categories.id', '=', 'subcategories.category_id')
            ->select('categories.name')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'name');

        $cumulativeUsers = [];
        $cumulativeProducts = [];
        $userTotal = 0;
        $productTotal = 0;

        foreach ($traffic as $day) {
            $userTotal += $day['users'];
            $productTotal += $day['products'];
            $cumulativeUsers[] = $userTotal;
            $cumulativeProducts[] = $productTotal;
        }

        $conversations = collect($data['chat']['messages_by_conversation'])->take(8)->values();

        $analyticsCharts = [
            'activity' => [
                'labels' => $activityLabels,
                'users' => $this->series($traffic, 'users'),
                'products' => $this->series($traffic, 'products'),
                'comments' => $this->series($traffic, 'comments'),
                'messages' => $this->series($traffic, 'messages'),
            ],
            'cumulative' => [
                'labels' => $activityLabels,
                'users' => $cumulativeUsers,
                'products' => $cumulativeProducts,
            ],
            'listingStatuses' => [
                'labels' => ['Approved', 'Pending', 'Rejected'],
                'values' => [
                    $productStatusCounts->get('approved', 0),
                    $productStatusCounts->get('pending', 0),
                    $productStatusCounts->get('rejected', 0),
                ],
            ],
            'reportStatuses' => [
                'labels' => ['Pending', 'Resolved', 'Dismissed'],
                'values' => [
                    $reportStatusCounts->get('pending', 0),
                    $reportStatusCounts->get('resolved', 0),
                    $reportStatusCounts->get('dismissed', 0),
                ],
            ],
            'categories' => [
                'labels' => $categoryListings->keys()->all(),
                'values' => $categoryListings->values()->all(),
            ],
            'conversations' => [
                'labels' => $conversations->pluck('label')->all(),
                'values' => $conversations->pluck('count')->all(),
            ],
        ];

        return view('admin.analytics', [
            'range' => $range,
            'kpis' => $this->kpis($traffic, $range),
            'analyticsCharts' => $analyticsCharts,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(int $days = 30): array
    {
        $totalUsers = User::count();
        $totalCategories = Category::count();
        $totalComments = Comment::count();
        $pendingReports = Report::where('status', 'pending')->count();
        $recentUsers = User::with('profile:id,user_id,avatar')
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'email', 'created_at', 'is_admin']);
        $recentReports = Report::with(['product' => fn ($q) => $q->select('id', 'name'), 'user:id,name'])
            ->latest()
            ->take(5)
            ->get(['id', 'user_id', 'product_id', 'reason', 'status', 'created_at']);

        $totalConversations = Conversation::count();
        $totalMessages = Message::count();
        $todayMessages = Message::where('created_at', '>=', now()->startOfDay())->count();

        $messagesGrouped = Message::where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at'])
            ->groupBy(fn ($m) => $m->created_at->toDateString())
            ->map->count();

        $messagesByDay = collect(range(6, 0))->map(function (int $i) use ($messagesGrouped) {
            $day = now()->subDays($i)->toDateString();

            return [
                'date' => $day,
                'count' => (int) ($messagesGrouped[$day] ?? 0),
            ];
        })->values()->all();

        $topConversations = Message::query()
            ->select('conversation_id')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('conversation_id')
            ->with('conversation.user1:id,name', 'conversation.user2:id,name')
            ->groupBy('conversation_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $messagesByConversation = $topConversations->map(function (Message $m) {
            $conversation = $m->conversation;

            return [
                'id' => $m->conversation_id,
                'label' => $conversation
                    ? ($conversation->user1?->name ?: 'User').' & '.($conversation->user2?->name ?: 'User')
                    : "Conversation #{$m->conversation_id}",
                'count' => (int) $m->total,
            ];
        })->values();

        $otherMessages = Message::whereNotNull('conversation_id')
            ->whereNotIn('conversation_id', $topConversations->pluck('conversation_id'))
            ->count();

        if ($otherMessages > 0) {
            $messagesByConversation->push([
                'id' => null,
                'label' => 'Other',
                'count' => $otherMessages,
            ]);
        }

        $since = now()->subDays($days - 1)->startOfDay();
        $daily = [];

        foreach (['users' => User::class, 'products' => Product::class, 'comments' => Comment::class, 'messages' => Message::class] as $key => $model) {
            $daily[$key] = $model::selectRaw('date(created_at) as d, count(*) as c')
                ->where('created_at', '>=', $since)
                ->groupBy('d')
                ->pluck('c', 'd');
        }

        $traffic = collect(range($days - 1, 0))->map(function (int $i) use ($daily) {
            $date = now()->subDays($i)->toDateString();

            return [
                'date' => $date,
                'users' => (int) ($daily['users'][$date] ?? 0),
                'products' => (int) ($daily['products'][$date] ?? 0),
                'comments' => (int) ($daily['comments'][$date] ?? 0),
                'messages' => (int) ($daily['messages'][$date] ?? 0),
            ];
        })->values()->all();

        return [
            'stats' => [
                'total_users' => $totalUsers,
                'total_categories' => $totalCategories,
                'total_comments' => $totalComments,
                'pending_reports' => $pendingReports,
            ],
            'recent_users' => $recentUsers,
            'recent_reports' => $recentReports,
            'traffic' => $traffic,
            'chat' => [
                'total_conversations' => $totalConversations,
                'total_messages' => $totalMessages,
                'today_messages' => $todayMessages,
                'messages_by_day' => $messagesByDay,
                'messages_by_conversation' => $messagesByConversation->all(),
            ],
        ];
    }

    /**
     * @return list<int>
     */
    private function series(array $traffic, string $key): array
    {
        return array_map(fn (array $row) => (int) $row[$key], $traffic);
    }

    /**
     * @param  list<int>  $users
     * @param  list<int>  $products
     * @param  list<string>  $dates
     * @param  array{width: int, height: int, padX: int, padT: int, padB: int}  $dims
     * @return array<string, mixed>
     */
    private function chart(array $users, array $products, array $dates, array $dims): array
    {
        $combined = array_merge($users, $products);
        $lo = (float) min($combined ?: [0]);
        $hi = (float) (max($combined) ?: 1);

        if ($lo == $hi) {
            if ($hi == 0.0) {
                $hi = 100.0;
            } else {
                $lo = $hi * 0.9;
                $hi *= 1.1;
            }
        }

        $pad = ($hi - $lo) * 0.12;
        $lo -= $pad;
        $hi += $pad;

        $innerW = $dims['width'] - $dims['padX'] * 2;
        $innerH = $dims['height'] - $dims['padT'] - $dims['padB'];

        $x = fn (int $i, int $n) => $dims['padX'] + ($i / max(1, $n - 1)) * $innerW;
        $y = fn (float $v) => $dims['padT'] + $innerH - (($v - $lo) / ($hi - $lo)) * $innerH;

        $n = count($users);
        $userPoints = [];
        $productPoints = [];

        foreach ($users as $i => $value) {
            $userPoints[] = ['x' => $x($i, $n), 'y' => $y((float) $value)];
        }

        foreach ($products as $i => $value) {
            $productPoints[] = ['x' => $x($i, $n), 'y' => $y((float) $value)];
        }

        $userPath = $this->smoothPath($userPoints);
        $productPath = $this->smoothPath($productPoints);
        $productArea = '';

        if ($productPoints !== []) {
            $productArea = $productPath
                .' L '.$this->format($productPoints[$n - 1]['x']).' '.$this->format($y((float) $lo))
                .' L '.$this->format($productPoints[0]['x']).' '.$this->format($y((float) $lo)).' Z';
        }

        $gridPath = '';
        $steps = [0, 0.25, 0.5, 0.75, 1];

        foreach ($steps as $fraction) {
            $gridPath .= sprintf('M %s %s H %s', $dims['padX'], $this->format($y($hi - (($hi - $lo) * $fraction))), $dims['width'] - $dims['padX']);
        }

        $segments = count($dates);

        return [
            'users' => $userPath,
            'products' => $productPath,
            'area' => $productArea,
            'grid' => $gridPath,
            'yLabels' => collect($steps)->map(fn ($f) => $this->format($hi - (($hi - $lo) * $f)))->all(),
            'xLabels' => collect($dates)->values()
                ->filter(fn ($d, $i) => $i % max(1, (int) ceil($segments / 6)) === 0)
                ->map(fn ($d) => Carbon::parse($d)->format('M j'))
                ->values()
                ->all(),
            'hi' => $hi,
            'lo' => $lo,
        ];
    }

    /**
     * @param  list<array{date: string, users: int, products: int, comments: int, messages: int}>  $traffic
     * @return array<string, mixed>
     */
    private function kpis(array $traffic, int $range): array
    {
        $users = $this->series($traffic, 'users');
        $products = $this->series($traffic, 'products');
        $comments = $this->series($traffic, 'comments');
        $messages = $this->series($traffic, 'messages');
        $newUsers = array_sum($users);
        $newListings = array_sum($products);
        $newComments = array_sum($comments);
        $totalMessages = array_sum($messages);
        $listingsToday = $products ? end($products) : 0;
        $messagesToday = $messages ? end($messages) : 0;

        return [
            'new_users' => $newUsers,
            'user_pct' => $this->trendPct($users),
            'avg_daily_users' => round($newUsers / max(1, $range), 1),
            'new_listings' => $newListings,
            'listing_pct' => $this->trendPct($products),
            'listings_today' => $listingsToday,
            'listings_share' => $newListings > 0 ? round($listingsToday / $newListings * 100, 1) : 0,
            'avg_daily_listings' => round($newListings / max(1, $range), 1),
            'new_comments' => $newComments,
            'comment_pct' => $this->trendPct($comments),
            'avg_daily_comments' => round($newComments / max(1, $range), 1),
            'messages_total' => $totalMessages,
            'message_pct' => $this->trendPct($messages),
            'messages_today' => $messagesToday,
            'messages_share' => $totalMessages > 0 ? round($messagesToday / $totalMessages * 100, 1) : 0,
            'avg_daily_messages' => round($totalMessages / max(1, $range), 1),
        ];
    }

    /**
     * @param  list<int>  $series
     * @return array{pct: string, dir: string}
     */
    private function trend(array $series): array
    {
        $pct = $this->trendPct($series);

        if ($pct['pct'] === '0.0') {
            return ['pct' => '0.0', 'dir' => 'flat'];
        }

        return $pct;
    }

    /**
     * @param  list<int>  $series
     * @return array{pct: string, dir: string}
     */
    private function trendPct(array $series): array
    {
        $n = count($series);

        if ($n < 2) {
            return ['pct' => '0.0', 'dir' => 'flat'];
        }

        $mid = intdiv($n, 2);
        $prev = array_sum(array_slice($series, 0, $mid));
        $next = array_sum(array_slice($series, $mid));

        if ($prev == 0) {
            return ['pct' => '0.0', 'dir' => 'flat'];
        }

        $pct = (($next - $prev) / $prev) * 100;

        if ($pct == 0) {
            return ['pct' => '0.0', 'dir' => 'flat'];
        }

        return ['pct' => number_format(abs($pct), 1), 'dir' => $pct > 0 ? 'up' : 'down'];
    }

    /**
     * @param  list<int>  $values
     * @return array{line: string, area: string}
     */
    private function sparkline(array $values): array
    {
        $w = 100;
        $h = 30;
        $padX = 2;
        $padT = 2;
        $padB = 2;

        $safe = $values === [] ? [0, 1] : $values;
        $lo = (float) min($safe);
        $hi = (float) max($safe);

        if ($lo == $hi) {
            $hi = $hi == 0.0 ? 1.0 : $hi * 1.2;
            $lo = $lo == 0.0 ? 0.0 : $lo * 0.8;
        }

        $innerW = $w - $padX * 2;
        $innerH = $h - $padT - $padB;

        $x = fn (int $i, int $n) => $padX + ($i / max(1, $n - 1)) * $innerW;
        $y = fn (float $v) => $padT + $innerH - (($v - $lo) / ($hi - $lo)) * $innerH;

        $points = [];

        foreach ($values as $i => $value) {
            $points[] = ['x' => $x($i, count($values)), 'y' => $y((float) $value)];
        }

        $line = $this->smoothPath($points);

        if ($points === []) {
            return ['line' => '', 'area' => ''];
        }

        $firstX = $points[0]['x'];
        $lastX = $points[count($points) - 1]['x'];
        $bottomY = $y($lo);

        $area = $line
            .' L '.$this->format($lastX).' '.$this->format($bottomY)
            .' L '.$this->format($firstX).' '.$this->format($bottomY).' Z';

        return ['line' => $line, 'area' => $area];
    }

    /**
     * @param  list<array{x: float, y: float}>  $points
     */
    private function smoothPath(array $points): string
    {
        $n = count($points);

        if ($n === 0) {
            return '';
        }

        $d = sprintf('M %s %s', $this->format($points[0]['x']), $this->format($points[0]['y']));

        for ($i = 0; $i < $n - 1; $i++) {
            $p0 = $points[max(0, $i - 1)];
            $p1 = $points[$i];
            $p2 = $points[$i + 1];
            $p3 = $points[min($n - 1, $i + 2)];

            $c1x = $p1['x'] + (($p2['x'] - $p0['x']) / 6);
            $c1y = $p1['y'] + (($p2['y'] - $p0['y']) / 6);
            $c2x = $p2['x'] - (($p3['x'] - $p1['x']) / 6);
            $c2y = $p2['y'] - (($p3['y'] - $p1['y']) / 6);

            $d .= sprintf(' C %s %s, %s %s, %s %s', $this->format($c1x), $this->format($c1y), $this->format($c2x), $this->format($c2y), $this->format($p2['x']), $this->format($p2['y']));
        }

        return $d;
    }

    private function format(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1), '0'), '.');
    }
}
