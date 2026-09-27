@extends('admin.layouts.app')

@section('title', 'Ads')

@section('crumb', 'Ads')

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Ads</h2>
            <p class="admin-page-subtitle">Banners shown at the top of the storefront homepage. Active ads rotate automatically.</p>
        </div>
        <a href="{{ route('admin.ads.create') }}" class="admin-button admin-button-primary">New ad</a>
    </div>

    <div class="admin-table-wrap mt-6">
        <table class="admin-table">
            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs uppercase tracking-wider text-neutral-400">
                <tr>
                    <th class="px-5 py-3 font-semibold">Banner</th>
                    <th class="px-5 py-3 font-semibold">Order</th>
                    <th class="px-5 py-3 font-semibold">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100">
                @forelse ($ads as $ad)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('storage/'.$ad->image) }}"
                                     alt=""
                                     class="h-10 w-20 rounded-md border border-neutral-200 object-cover">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.ads.edit', $ad) }}"
                                       class="block truncate font-medium text-neutral-900 hover:underline">{{ $ad->headline ?: ($ad->subtext ?: 'Ad #'.$ad->id) }}</a>
                                    @if ($ad->subtext)
                                        <p class="max-w-sm truncate text-xs text-neutral-400">{{ $ad->subtext }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 font-medium text-neutral-600">{{ $ad->sort_order }}</td>
                        <td class="px-5 py-3">
                            <span class="admin-badge {{ $ad->is_active ? 'admin-badge-success' : 'admin-badge-muted' }}">
                                {{ $ad->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.ads.edit', $ad) }}" class="admin-button admin-button-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.ads.toggle-active', $ad) }}">
                                    @csrf
                                    <button type="submit" class="admin-button admin-button-secondary">
                                        {{ $ad->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.ads.destroy', $ad) }}" data-confirm="Delete this ad?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-button admin-button-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="admin-table-empty">No ads yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination">
            {{ $ads->links() }}
        </div>
    </div>
@endsection
