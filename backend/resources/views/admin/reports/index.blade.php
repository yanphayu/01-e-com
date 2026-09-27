@extends('admin.layouts.app')

@section('title', 'Reports')

@section('crumb', 'Reports')

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Reports</h2>
            <p class="admin-page-subtitle">
                {{ number_format($reports->total()) }} {{ \Illuminate\Support\Str::plural('report', $reports->total()) }} submitted by members
            </p>
        </div>
    </div>
    <div class="admin-export-bar">
        @include('admin.partials.export-buttons', ['resource' => 'reports'])
    </div>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="admin-filter admin-filter-grid admin-filter-grid--compact">
        <div>
            <label for="search" class="mb-1 block text-xs font-semibold text-neutral-500">Search</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Listing, reporter, or reason…"
                   class="admin-control">
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-semibold text-neutral-500">Status</label>
            <select id="status" name="status" class="admin-control">
                <option value="">All statuses</option>
                @foreach (['pending', 'resolved', 'dismissed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="admin-button admin-button-primary">Filter</button>
            <a href="{{ route('admin.reports.index') }}" class="admin-button admin-button-secondary">Reset</a>
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs uppercase tracking-wider text-neutral-400">
                <tr>
                    <th class="px-5 py-3 font-semibold">Reported listing</th>
                    <th class="px-5 py-3 font-semibold">Reporter</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 font-semibold">Submitted</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody id="admin-reports-table-body" data-infinite-scroll-body class="divide-y divide-neutral-100">
                @forelse ($reports as $report)
                    @php
                        $image = $report->product?->images->first();
                        $imageUrl = $image
                            ? (str_starts_with($image->image, 'http') ? $image->image : asset('storage/'.$image->image))
                            : null;
                    @endphp
                    <tr class="hover:bg-neutral-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if ($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="" class="admin-product-image" loading="lazy">
                                @else
                                    <span class="admin-product-fallback">{{ $report->product ? strtoupper(substr($report->product->name, 0, 1)) : '?' }}</span>
                                @endif
                                <div class="min-w-0">
                                    @if ($report->product)
                                        <a href="{{ route('admin.products.show', $report->product) }}" class="block max-w-[320px] truncate font-medium text-neutral-900 hover:text-neutral-600">
                                            {{ $report->product->name }}
                                        </a>
                                    @else
                                        <span class="font-medium text-neutral-400">Deleted product</span>
                                    @endif
                                    <p class="mt-1 max-w-[360px] truncate text-xs text-neutral-500">
                                        <span class="font-semibold text-neutral-700">{{ $report->reason }}</span>
                                        @if ($report->details)
                                            — {{ $report->details }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="min-w-0">
                                <p class="font-medium text-neutral-900">{{ $report->user->name ?? 'Unknown user' }}</p>
                                <p class="truncate text-xs text-neutral-400">{{ $report->user->email ?? 'No email available' }}</p>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="admin-badge {{ $report->status === 'pending' ? 'admin-badge-warning' : ($report->status === 'resolved' ? 'admin-badge-success' : 'admin-badge-muted') }}">
                                {{ ucfirst($report->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-xs font-medium text-neutral-600">{{ $report->created_at->diffForHumans() }}</p>
                            <p class="mt-1 text-xs text-neutral-400">{{ $report->created_at->format('M j, Y') }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                @if ($report->product)
                                    <a href="{{ route('admin.products.show', $report->product) }}" class="admin-button admin-button-secondary">View</a>
                                @endif
                                @if ($report->status === 'pending')
                                    <form method="POST" action="{{ route('admin.reports.resolve', $report) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="resolved">
                                        <button type="submit" class="admin-button admin-button-success">Resolve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.reports.resolve', $report) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="dismissed">
                                        <button type="submit" class="admin-button admin-button-secondary">Dismiss</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="admin-table-empty">No reports match your filters</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination admin-pagination--scroll"
             data-admin-infinite-scroll
             data-infinite-scroll-target="admin-reports-table-body"
             data-infinite-scroll-next="{{ $reports->nextPageUrl() }}">
            <div class="admin-infinite-scroll-status" data-infinite-scroll-status aria-live="polite">
                @if ($reports->hasMorePages())
                    <span class="admin-infinite-scroll-spinner" data-infinite-scroll-spinner hidden aria-hidden="true"></span>
                    <span data-infinite-scroll-label>Scroll to load more</span>
                    <button type="button" class="admin-button admin-button-secondary" data-infinite-scroll-button hidden>Load more</button>
                @else
                    <span data-infinite-scroll-label>All records loaded</span>
                @endif
            </div>
        </div>
    </div>
@endsection
