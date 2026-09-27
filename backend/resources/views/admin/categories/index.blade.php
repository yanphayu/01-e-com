@extends('admin.layouts.app')

@section('title', 'Categories')

@section('crumb', 'Categories')

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Categories</h2>
            <p class="admin-page-subtitle">Top-level marketplace categories</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="admin-button admin-button-primary">New category</a>
    </div>
    <div class="admin-export-bar">
        @include('admin.partials.export-buttons', ['resource' => 'categories'])
    </div>

    <form method="GET" action="{{ route('admin.categories.index') }}" class="admin-filter mt-6 flex max-w-md gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories…"
               class="admin-control">
        <button type="submit" class="admin-button admin-button-primary">Filter</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs uppercase tracking-wider text-neutral-400">
                <tr>
                    <th class="px-5 py-3 font-semibold">Name</th>
                    <th class="px-5 py-3 font-semibold">Subcategories</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody id="admin-categories-table-body" data-infinite-scroll-body class="divide-y divide-neutral-100">
                @forelse ($categories as $category)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="font-medium text-neutral-900 hover:underline">{{ $category->name }}</a>
                            @if ($category->description)
                                <p class="max-w-md truncate text-xs text-neutral-400">{{ $category->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 font-medium text-neutral-600">{{ number_format($category->subcategories_count) }}</td>
                        <td class="px-5 py-3">
                            <span class="admin-badge {{ $category->is_active ? 'admin-badge-success' : 'admin-badge-muted' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="admin-button admin-button-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.toggle-active', $category) }}">
                                    @csrf
                                    <button type="submit" class="admin-button admin-button-secondary">
                                        {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Delete this category?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-button admin-button-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="admin-table-empty">No categories yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination admin-pagination--scroll"
             data-admin-infinite-scroll
             data-infinite-scroll-target="admin-categories-table-body"
             data-infinite-scroll-next="{{ $categories->nextPageUrl() }}">
            <div class="admin-infinite-scroll-status" data-infinite-scroll-status aria-live="polite">
                @if ($categories->hasMorePages())
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
