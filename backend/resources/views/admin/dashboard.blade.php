@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}</h2>
            <p class="admin-page-subtitle">Here is what is happening across TRINITY today.</p>
        </div>
        <div class="flex items-center gap-3 text-xs">
            <span class="admin-date-chip">{{ now()->format('l, M j') }}</span>
            <a href="{{ route('admin.reports.index', ['status' => 'pending']) }}"
               class="admin-report-chip">
                {{ $data['stats']['pending_reports'] }} pending report{{ $data['stats']['pending_reports'] === 1 ? '' : 's' }}
            </a>
        </div>
    </div>

    <div class="admin-stats-grid">
        @php
            $cards = [
                ['label' => 'Total Users', 'value' => number_format($data['stats']['total_users']), 'spark' => $sparks['users'] ?? null, 'trend' => $trends['users'] ?? null, 'color' => 'admin-stat-icon--green'],
                ['label' => 'New Listings (30d)', 'value' => number_format($totals['products']), 'spark' => $sparks['products'] ?? null, 'trend' => $trends['products'] ?? null, 'color' => 'admin-stat-icon--blue'],
                ['label' => 'Comments (30d)', 'value' => number_format($totals['comments']), 'spark' => $sparks['comments'] ?? null, 'trend' => $trends['comments'] ?? null, 'color' => 'admin-stat-icon--amber'],
                ['label' => 'Pending Reports', 'value' => number_format($data['stats']['pending_reports']), 'spark' => null, 'trend' => null, 'color' => 'admin-stat-icon--red'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="admin-stat-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="admin-stat-label">{{ $card['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight">{{ $card['value'] }}</p>
                    </div>
                    <span class="admin-stat-icon {{ $card['color'] }}">
                        @if ($card['label'] === 'Total Users')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @elseif ($card['label'] === 'New Listings (30d)')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        @elseif ($card['label'] === 'Comments (30d)')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </span>
                </div>

            </div>
        @endforeach
    </div>

    <div class="mt-6 admin-card admin-card--padded">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="admin-section-title">Activity — last 30 days</h3>
                <p class="text-xs text-neutral-400">{{ now()->subDays(29)->format('M j') }} – {{ now()->format('M j, Y') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-xs">
                <span class="inline-flex items-center gap-1.5"><span class="admin-legend-dot admin-legend-dot--secondary"></span> New users</span>
                <span class="inline-flex items-center gap-1.5"><span class="admin-legend-dot admin-legend-dot--primary"></span> New listings</span>
                <span class="inline-flex items-center gap-1.5"><span class="admin-legend-dot admin-legend-dot--amber"></span> Comments</span>
                <span class="inline-flex items-center gap-1.5"><span class="admin-legend-dot admin-legend-dot--danger"></span> Messages</span>
                <a href="{{ route('admin.analytics') }}" class="admin-section-link">Full analytics →</a>
            </div>
        </div>

        <div class="admin-chart-canvas mt-4">
            <canvas data-admin-activity-chart role="img" aria-label="Line chart of new users, new listings, comments, and messages over the last 30 days"></canvas>
        </div>
        <script type="application/json" id="admin-activity-chart-data">@json($chartData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="text-sm font-bold">Recent users</h3>
                <a href="{{ route('admin.users.index') }}" class="admin-section-link">View all →</a>
            </div>
            <ul class="admin-list">
                @forelse ($data['recent_users'] as $user)
                    <li class="admin-list-item">
                        @include('admin.partials.avatar', [
                            'avatar' => $user->profile?->avatar,
                            'name' => $user->name,
                            'primary' => $user->is_admin,
                            'class' => 'admin-avatar object-cover',
                        ])
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ $user->name }}</p>
                            <p class="truncate text-xs text-neutral-400">{{ $user->email }}</p>
                        </div>
                        <span class="ml-auto text-xs text-neutral-400">{{ $user->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="admin-list-item justify-center text-sm text-neutral-400">No users yet</li>
                @endforelse
            </ul>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="text-sm font-bold">Recent reports</h3>
                <a href="{{ route('admin.reports.index') }}" class="admin-section-link">View all →</a>
            </div>
            <ul class="admin-list">
                @forelse ($data['recent_reports'] as $report)
                    <li class="admin-list-item">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ $report->product->name ?? 'Deleted product' }}</p>
                            <p class="truncate text-xs text-neutral-400">{{ $report->reason }} · by {{ $report->user->name ?? 'Unknown' }}</p>
                        </div>
                        <span class="admin-badge {{ $report->status === 'pending' ? 'admin-badge-danger' : ($report->status === 'resolved' ? 'admin-badge-success' : 'admin-badge-muted') }}">
                            {{ ucfirst($report->status) }}
                        </span>
                    </li>
                @empty
                    <li class="admin-list-item justify-center text-sm text-neutral-400">No reports</li>
                @endforelse
            </ul>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h3 class="text-sm font-bold">Chat activity</h3>
            </div>
            <div class="space-y-4 px-5 py-4 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-neutral-500">Conversations</span>
                    <span class="font-bold">{{ number_format($data['chat']['total_conversations']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-neutral-500">Total messages</span>
                    <span class="font-bold">{{ number_format($data['chat']['total_messages']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-neutral-500">Messages today</span>
                    <span class="admin-trend-up">{{ number_format($data['chat']['today_messages']) }}</span>
                </div>

                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-neutral-400">This week</p>
                    <div class="admin-chart-canvas admin-chart-canvas--compact">
                        <canvas data-admin-chat-chart role="img" aria-label="Bar chart of messages sent over the last seven days"></canvas>
                    </div>
                </div>
                <script type="application/json" id="admin-chat-chart-data">@json($chatChartData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
            </div>
        </div>
    </div>
@endsection