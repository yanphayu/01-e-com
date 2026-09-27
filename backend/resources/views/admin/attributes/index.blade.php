@extends('admin.layouts.app')

@section('title', 'Attributes')

@section('crumb', 'Attributes')

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Attributes</h2>
            <p class="admin-page-subtitle">Spec attributes available to product models</p>
        </div>
        <a href="{{ route('admin.attributes.create') }}" class="admin-button admin-button-primary">New attribute</a>
    </div>
    <div class="admin-export-bar">
        @include('admin.partials.export-buttons', ['resource' => 'attributes'])
    </div>

    <form method="GET" action="{{ route('admin.attributes.index') }}" class="admin-filter mt-6 flex max-w-md gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search attributes…"
               class="admin-control">
        <button type="submit" class="admin-button admin-button-primary">Filter</button>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs uppercase tracking-wider text-neutral-400">
                <tr>
                    <th class="px-5 py-3 font-semibold">Name</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody id="admin-attributes-table-body" data-infinite-scroll-body class="divide-y divide-neutral-100">
                @forelse ($attributes as $attribute)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.attributes.edit', $attribute) }}" class="font-medium text-neutral-900 hover:underline">{{ $attribute->name }}</a>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.attributes.edit', $attribute) }}" class="admin-button admin-button-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.attributes.destroy', $attribute) }}" data-confirm="Delete this attribute?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-button admin-button-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="admin-table-empty">No attributes yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination admin-pagination--scroll"
             data-admin-infinite-scroll
             data-infinite-scroll-target="admin-attributes-table-body"
             data-infinite-scroll-next="{{ $attributes->nextPageUrl() }}">
            <div class="admin-infinite-scroll-status" data-infinite-scroll-status aria-live="polite">
                @if ($attributes->hasMorePages())
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
