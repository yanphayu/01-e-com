@extends('admin.layouts.app')

@section('title', 'Analytics')

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Analytics</h2>
            <p class="admin-page-subtitle">Community growth, catalog, moderation, and messaging insights.</p>
        </div>
        <div class="admin-tabs">
            @foreach ([7, 30, 90] as $r)
                <a href="{{ route('admin.analytics', ['range' => $r]) }}" class="admin-tab {{ $range === $r ? 'is-active' : '' }}">
                    {{ $r }}d
                </a>
            @endforeach
        </div>
    </div>

    <div class="admin-stats-grid">
        <div class="admin-stat-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="admin-stat-label">New users</p>
                    <p class="admin-stat-value">{{ number_format($kpis['new_users']) }}</p>
                </div>
                <span class="admin-stat-icon admin-stat-icon--green">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="admin-stat-label">New listings</p>
                    <p class="admin-stat-value">{{ number_format($kpis['new_listings']) }}</p>
                </div>
                <span class="admin-stat-icon admin-stat-icon--blue">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="admin-stat-label">New comments</p>
                    <p class="admin-stat-value">{{ number_format($kpis['new_comments']) }}</p>
                </div>
                <span class="admin-stat-icon admin-stat-icon--amber">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </span>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="admin-stat-label">Chat messages</p>
                    <p class="admin-stat-value">{{ number_format($kpis['messages_total']) }}</p>
                </div>
                <span class="admin-stat-icon admin-stat-icon--green">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </span>
            </div>
        </div>
    </div>

    <div class="mt-6 admin-card admin-card--padded">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="admin-section-title">Platform activity</h3>
                <p class="mt-1 text-xs text-neutral-400">Daily users, listings, comments, and messages over {{ $range }} days</p>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-xs text-neutral-500">
                <span class="inline-flex items-center gap-1.5"><span class="admin-legend-dot admin-legend-dot--secondary"></span> Users</span>
                <span class="inline-flex items-center gap-1.5"><span class="admin-legend-dot admin-legend-dot--primary"></span> Listings</span>
                <span class="inline-flex items-center gap-1.5"><span class="admin-legend-dot admin-legend-dot--amber"></span> Comments</span>
                <span class="inline-flex items-center gap-1.5"><span class="admin-legend-dot admin-legend-dot--danger"></span> Messages</span>
            </div>
        </div>
        <div class="mt-5 admin-chart-frame admin-chart-frame--tall">
            <canvas data-admin-activity-chart role="img" aria-label="Daily platform activity for the last {{ $range }} days"></canvas>
        </div>
        <script type="application/json" id="admin-activity-chart-data">@json($analyticsCharts['activity'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)</script>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="admin-card admin-card--padded">
            <h3 class="admin-section-title">Listings by status</h3>
            <p class="mt-1 text-xs text-neutral-400">All-time catalog distribution</p>
            <div class="mt-5 admin-chart-frame admin-chart-frame--doughnut">
                <canvas data-admin-listing-status-chart role="img" aria-label="All-time listing status distribution"></canvas>
            </div>
        </div>

        <div class="admin-card admin-card--padded">
            <h3 class="admin-section-title">Reports by status</h3>
            <p class="mt-1 text-xs text-neutral-400">All-time moderation distribution</p>
            <div class="mt-5 admin-chart-frame admin-chart-frame--doughnut">
                <canvas data-admin-report-status-chart role="img" aria-label="All-time report status distribution"></canvas>
            </div>
        </div>

        <div class="admin-card admin-card--padded">
            <h3 class="admin-section-title">Listings by category</h3>
            <p class="mt-1 text-xs text-neutral-400">Top eight categories across the catalog</p>
            <div class="mt-5 admin-chart-frame admin-chart-frame--horizontal">
                <canvas data-admin-category-chart role="img" aria-label="Top listing categories"></canvas>
            </div>
        </div>

        <div class="admin-card admin-card--padded">
            <h3 class="admin-section-title">Most active conversations</h3>
            <p class="mt-1 text-xs text-neutral-400">All-time message volume by conversation</p>
            <div class="mt-5 admin-chart-frame admin-chart-frame--horizontal">
                <canvas data-admin-conversation-chart role="img" aria-label="Most active conversations"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-6 admin-card admin-card--padded">
        <h3 class="admin-section-title">Cumulative growth</h3>
        <p class="mt-1 text-xs text-neutral-400">Running new-user and new-listing totals over {{ $range }} days</p>
        <div class="mt-5 admin-chart-frame admin-chart-frame--tall">
            <canvas data-admin-cumulative-chart role="img" aria-label="Cumulative user and listing growth for {{ $range }} days"></canvas>
        </div>
    </div>

    <script id="admin-analytics-chart-data" type="application/json">@json($analyticsCharts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
@endsection
