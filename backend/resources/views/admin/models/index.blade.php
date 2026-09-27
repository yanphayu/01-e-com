@extends('admin.layouts.app')

@section('title', 'Models')

@section('crumb', 'Models')

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Models</h2>
            <p class="admin-page-subtitle">Product models with linked attributes</p>
        </div>
        <a href="{{ route('admin.models.create') }}" class="admin-button admin-button-primary">New model</a>
    </div>
    <div class="admin-export-bar">
        @include('admin.partials.export-buttons', ['resource' => 'models'])
    </div>

    <form method="GET" action="{{ route('admin.models.index') }}" class="admin-filter admin-filter-grid">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search models…"
               class="admin-control">
        <select name="brand_id" class="admin-control">
            <option value="">All brands</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>{{ $brand->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-button admin-button-primary">Filter</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs uppercase tracking-wider text-neutral-400">
                <tr>
                    <th class="px-5 py-3 font-semibold">Name</th>
                    <th class="px-5 py-3 font-semibold">Brand</th>
                    <th class="px-5 py-3 font-semibold">Attributes</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody id="admin-models-table-body" data-infinite-scroll-body class="divide-y divide-neutral-100">
                @forelse ($models as $model)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.models.edit', $model) }}" class="font-medium text-neutral-900 hover:underline">{{ $model->name }}</a>
                        </td>
                        <td class="px-5 py-3 text-neutral-600">{{ $model->brand->name ?? '—' }}</td>
                        <td class="px-5 py-3">
                            @if ($model->attributes_count ?? false)
                                <span class="text-xs text-neutral-500">{{ number_format($model->attributes_count) }} linked</span>
                            @else
                                <span class="text-xs text-neutral-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.models.edit', $model) }}" class="admin-button admin-button-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.models.destroy', $model) }}" data-confirm="Delete this model?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-button admin-button-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="admin-table-empty">No models yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination admin-pagination--scroll"
             data-admin-infinite-scroll
             data-infinite-scroll-target="admin-models-table-body"
             data-infinite-scroll-next="{{ $models->nextPageUrl() }}">
            <div class="admin-infinite-scroll-status" data-infinite-scroll-status aria-live="polite">
                @if ($models->hasMorePages())
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
