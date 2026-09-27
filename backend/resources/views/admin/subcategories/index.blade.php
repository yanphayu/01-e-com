@extends('admin.layouts.app')

@section('title', 'Subcategories')

@section('crumb', 'Subcategories')

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Subcategories</h2>
            <p class="admin-page-subtitle">Child categories with optional brand/model attributes</p>
        </div>
        <a href="{{ route('admin.subcategories.create') }}" class="admin-button admin-button-primary">New subcategory</a>
    </div>
    <div class="admin-export-bar">
        @include('admin.partials.export-buttons', ['resource' => 'subcategories'])
    </div>

    <form method="GET" action="{{ route('admin.subcategories.index') }}" class="admin-filter admin-filter-grid">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search subcategories…"
               class="admin-control">
        <select name="category_id" class="admin-control">
            <option value="">All categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="admin-button admin-button-primary">Filter</button>
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs uppercase tracking-wider text-neutral-400">
                <tr>
                    <th class="px-5 py-3 font-semibold">Name</th>
                    <th class="px-5 py-3 font-semibold">Category</th>
                    <th class="px-5 py-3 font-semibold">Products</th>
                    <th class="px-5 py-3 font-semibold">Extras</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody id="admin-subcategories-table-body" data-infinite-scroll-body class="divide-y divide-neutral-100">
                @forelse ($subcategories as $subcategory)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.subcategories.edit', $subcategory) }}" class="font-medium text-neutral-900 hover:underline">{{ $subcategory->name }}</a>
                        </td>
                        <td class="px-5 py-3 text-neutral-600">{{ $subcategory->category->name ?? '—' }}</td>
                        <td class="px-5 py-3 font-medium text-neutral-600">{{ number_format($subcategory->products_count) }}</td>
                        <td class="px-5 py-3 text-xs text-neutral-500">
                            {{ collect($subcategory->has_brand ? ['brand'] : [])->merge($subcategory->has_model ? ['model'] : [])->implode(', ') ?: '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="admin-badge {{ $subcategory->is_active ? 'admin-badge-success' : 'admin-badge-muted' }}">
                                {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.subcategories.edit', $subcategory) }}" class="admin-button admin-button-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.subcategories.toggle-active', $subcategory) }}">
                                    @csrf
                                    <button type="submit" class="admin-button admin-button-secondary">
                                        {{ $subcategory->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.subcategories.destroy', $subcategory) }}" data-confirm="Delete this subcategory?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-button admin-button-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="admin-table-empty">No subcategories yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination admin-pagination--scroll"
             data-admin-infinite-scroll
             data-infinite-scroll-target="admin-subcategories-table-body"
             data-infinite-scroll-next="{{ $subcategories->nextPageUrl() }}">
            <div class="admin-infinite-scroll-status" data-infinite-scroll-status aria-live="polite">
                @if ($subcategories->hasMorePages())
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
