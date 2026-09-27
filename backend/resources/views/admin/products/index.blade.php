@extends('admin.layouts.app')

@section('title', 'Products')

@section('crumb', 'Products')

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Products</h2>
            <p class="admin-page-subtitle">{{ number_format($products->total()) }} listings across the marketplace</p>
        </div>
    </div>
    <div class="admin-export-bar">
        @include('admin.partials.export-buttons', ['resource' => 'products'])
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="admin-filter admin-filter-grid admin-filter-grid--four">
        <div>
            <label for="search" class="mb-1 block text-xs font-semibold text-neutral-500">Search</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Product name…"
                   class="admin-control">
        </div>
        <div>
            <label for="status" class="mb-1 block text-xs font-semibold text-neutral-500">Status</label>
            <select id="status" name="status" class="admin-control">
                <option value="">All statuses</option>
                @foreach (['pending', 'approved', 'rejected'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="category_id" class="mb-1 block text-xs font-semibold text-neutral-500">Category</label>
            <select id="category_id" name="category_id" class="admin-control">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <div class="flex-1">
                <label for="is_active" class="mb-1 block text-xs font-semibold text-neutral-500">Visibility</label>
                <select id="is_active" name="is_active" class="admin-control">
                    <option value="">All</option>
                    <option value="1" @selected(request('is_active') === '1')>Active</option>
                    <option value="0" @selected(request('is_active') === '0')>Hidden</option>
                </select>
            </div>
            <button type="submit" class="admin-button admin-button-primary">Filter</button>
            <a href="{{ route('admin.products.index') }}" class="admin-button admin-button-secondary">Reset</a>
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs uppercase tracking-wider text-neutral-400">
                <tr>
                    <th class="px-5 py-3 font-semibold">Product</th>
                    <th class="px-5 py-3 font-semibold">Seller</th>
                    <th class="px-5 py-3 font-semibold">Category</th>
                    <th class="px-5 py-3 font-semibold">Price</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3 font-semibold">Listed</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody id="admin-products-table-body" data-infinite-scroll-body class="divide-y divide-neutral-100">
                @forelse ($products as $product)
                    @php
                        $thumb = $product->images->first();
                        $thumbUrl = $thumb ? (str_starts_with($thumb->image, 'http') ? $thumb->image : asset('storage/'.$thumb->image)) : null;
                    @endphp
                    <tr class="hover:bg-neutral-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.products.show', $product) }}" class="flex items-center gap-3">
                                @if ($thumbUrl)
                                    <img src="{{ $thumbUrl }}" alt="" class="admin-product-image" loading="lazy">
                                @else
                                    <span class="admin-product-fallback">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-neutral-900 hover:text-neutral-600">{{ $product->name }}</p>
                                    <span class="admin-badge {{ $product->is_active ? 'admin-badge-info' : 'admin-badge-muted' }}">{{ $product->is_active ? 'Visible' : 'Hidden' }}</span>
                                </div>
                            </a>
                        </td>
                        <td class="px-5 py-3 text-neutral-600">{{ $product->user->name ?? 'Deleted user' }}</td>
                        <td class="px-5 py-3 text-neutral-600">
                            {{ $product->subcategory->category->name ?? '—' }} / {{ $product->subcategory->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 font-semibold">${{ number_format($product->price, 2) }}</td>
                        <td class="px-5 py-3">
                            <span class="admin-badge {{ $product->status === 'approved' ? 'admin-badge-success' : ($product->status === 'pending' ? 'admin-badge-warning' : 'admin-badge-danger') }}">
                                {{ ucfirst($product->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-neutral-400">{{ $product->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.products.show', $product) }}" class="admin-button admin-button-secondary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="admin-table-empty">No products match your filters</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination admin-pagination--scroll"
             data-admin-infinite-scroll
             data-infinite-scroll-target="admin-products-table-body"
             data-infinite-scroll-next="{{ $products->nextPageUrl() }}">
            <div class="admin-infinite-scroll-status" data-infinite-scroll-status aria-live="polite">
                @if ($products->hasMorePages())
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
