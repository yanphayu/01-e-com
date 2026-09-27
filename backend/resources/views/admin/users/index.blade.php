@extends('admin.layouts.app')

@section('title', 'Users')

@section('crumb', 'Users')

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Users</h2>
            <p class="admin-page-subtitle">{{ number_format($users->total()) }} registered accounts</p>
        </div>
    </div>
    <div class="admin-export-bar">
        @include('admin.partials.export-buttons', ['resource' => 'users'])
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="admin-filter admin-filter-grid">
        <div>
            <label for="search" class="mb-1 block text-xs font-semibold text-neutral-500">Search</label>
            <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Name or email…"
                   class="admin-control">
        </div>
        <div>
            <label for="is_admin" class="mb-1 block text-xs font-semibold text-neutral-500">Role</label>
            <select id="is_admin" name="is_admin" class="admin-control">
                <option value="">All roles</option>
                <option value="1" @selected(request('is_admin') === '1')>Admins</option>
                <option value="0" @selected(request('is_admin') === '0')>Members</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="admin-button admin-button-primary">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="admin-button admin-button-secondary">Reset</a>
        </div>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead class="border-b border-neutral-200 bg-neutral-50 text-xs uppercase tracking-wider text-neutral-400">
                <tr>
                    <th class="px-5 py-3 font-semibold">User</th>
                    <th class="px-5 py-3 font-semibold">Role</th>
                    <th class="px-5 py-3 font-semibold">Products</th>
                    <th class="px-5 py-3 font-semibold">Joined</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody id="admin-users-table-body" data-infinite-scroll-body class="divide-y divide-neutral-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-neutral-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @include('admin.partials.avatar', [
                                    'avatar' => $user->profile?->avatar,
                                    'name' => $user->name,
                                    'primary' => $user->is_admin,
                                    'class' => 'admin-avatar object-cover',
                                ])
                                <div class="min-w-0">
                                    <p class="font-medium text-neutral-900">{{ $user->name }}</p>
                                    <p class="truncate text-xs text-neutral-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="admin-badge {{ $user->is_admin ? 'admin-badge-info' : 'admin-badge-muted' }}">
                                {{ $user->is_admin ? 'Admin' : 'Member' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 font-medium text-neutral-600">{{ number_format($user->products_count) }}</td>
                        <td class="px-5 py-3 text-xs text-neutral-400">{{ $user->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.users.show', $user) }}" class="admin-button admin-button-secondary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="admin-table-empty">No users match your filters</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="admin-pagination admin-pagination--scroll"
             data-admin-infinite-scroll
             data-infinite-scroll-target="admin-users-table-body"
             data-infinite-scroll-next="{{ $users->nextPageUrl() }}">
            <div class="admin-infinite-scroll-status" data-infinite-scroll-status aria-live="polite">
                @if ($users->hasMorePages())
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
