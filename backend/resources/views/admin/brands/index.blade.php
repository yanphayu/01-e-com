@extends('admin.layouts.app')

@section('title', 'Brands')

@section('crumb', 'Brands')

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Brands</h2>
            <p class="admin-page-subtitle">Brands organized by subcategory</p>
        </div>
        <a href="{{ route('admin.brands.create') }}" class="admin-button admin-button-primary">New brand</a>
    </div>
    <div class="admin-export-bar">
        @include('admin.partials.export-buttons', ['resource' => 'brands'])
    </div>

    <form method="GET" action="{{ route('admin.brands.index') }}" class="admin-filter admin-filter-grid">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search brands…"
               class="admin-control">
        <select name="subcategory_id" class="admin-control">
            <option value="">All subcategories</option>
            @foreach ($subcategories as $subcategory)
                <option value="{{ $subcategory->id }}" @selected(request('subcategory_id') == $subcategory->id)>{{ $subcategory->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-button admin-button-primary">Filter</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs uppercase tracking-wider text-neutral-400">
                <tr>
                    <th class="px-5 py-3 font-semibold">Name</th>
                    <th class="px-5 py-3 font-semibold">Subcategory</th>
                    <th class="px-5 py-3 font-semibold">Models</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody id="admin-brands-table-body" data-infinite-scroll-body class="divide-y divide-neutral-100">
                @forelse ($brands as $brand)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.brands.edit', $brand) }}" class="font-medium text-neutral-900 hover:underline">{{ $brand->name }}</a>
                        </td>
                        <td class="px-5 py-3 text-neutral-600">{{ $brand->subcategory->name ?? '—' }}</td>
                        <td class="px-5 py-3 font-medium text-neutral-600">{{ number_format($brand->models_count) }}</td>
                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.brands.edit', $brand) }}" class="admin-button admin-button-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" data-confirm="Delete this brand?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-button admin-button-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="admin-table-empty">No brands yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination admin-pagination--scroll"
             data-admin-infinite-scroll
             data-infinite-scroll-target="admin-brands-table-body"
             data-infinite-scroll-next="{{ $brands->nextPageUrl() }}">
            <div class="admin-infinite-scroll-status" data-infinite-scroll-status aria-live="polite">
                @if ($brands->hasMorePages())
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
