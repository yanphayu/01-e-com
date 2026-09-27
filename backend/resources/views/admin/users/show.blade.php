@extends('admin.layouts.app')

@section('title', $user->name)

@section('crumb')
    <a href="{{ route('admin.users.index') }}" class="hover:text-neutral-900 hover:underline">Users</a>
    <span class="mx-2 text-neutral-300">/</span>{{ str()->limit($user->name, 30) }}
@endsection

@section('content')
    <div class="admin-page-heading">
        <div class="flex items-center gap-4">
            @include('admin.partials.avatar', [
                'avatar' => $user->profile?->avatar,
                'name' => $user->name,
                'primary' => $user->is_admin,
                'class' => 'admin-avatar admin-avatar--large object-cover',
            ])
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="admin-page-title">{{ $user->name }}</h2>
                    <span class="admin-badge {{ $user->is_admin ? 'admin-badge-info' : 'admin-badge-muted' }}">
                        {{ $user->is_admin ? 'Admin' : 'Member' }}
                    </span>
                </div>
                <p class="admin-page-subtitle">{{ $user->email }} · joined {{ $user->created_at->format('M j, Y') }}</p>
            </div>
        </div>

        <div class="admin-heading-actions">
            <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}">
                @csrf
                <button type="submit" class="admin-button admin-button-secondary">
                    {{ $user->is_admin ? 'Remove admin' : 'Make admin' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Delete this user and all their data?">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-button admin-button-danger">Delete</button>
            </form>
        </div>
    </div>

    <div class="admin-profile-grid">
        <div class="admin-card admin-card--padded">
            <h3 class="admin-section-title">Activity</h3>
            @php
                $activityLabels = ['Products', 'Comments', 'Messages'];
                $activityValues = [
                    $user->products->count(),
                    $user->comments->count(),
                    $user->messages->count(),
                ];
            @endphp
            <div class="mt-4 h-[260px]">
                <canvas data-admin-user-activity-chart aria-label="Products, comments and messages" role="img"></canvas>
            </div>
            <script id="admin-user-activity-chart-data" type="application/json">{!! json_encode(['labels' => $activityLabels, 'values' => $activityValues], JSON_UNESCAPED_UNICODE) !!}</script>
        </div>

        <div class="admin-card admin-card--padded flex flex-col">
            <h3 class="admin-section-title">Edit user</h3>
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="admin-form admin-form--bare mt-5 gap-6 flex-1 content-center">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="mb-1 block text-xs font-semibold text-neutral-500">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           class="admin-control">
                </div>
                <div>
                    <label for="email" class="mb-1 block text-xs font-semibold text-neutral-500">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="admin-control">
                </div>

                @if ($errors->any())
                    <div class="admin-error">{{ $errors->first() }}</div>
                @endif

                <button type="submit" class="admin-button admin-button-primary admin-button-block">Save changes</button>
            </form>
        </div>
    </div>

    <div class="mt-6 admin-card admin-card--padded">
        <h3 class="admin-section-title">Listings by {{ $user->name }}</h3>
        <div class="admin-product-grid">
            @forelse ($user->products->take(6) as $product)
                <a href="{{ route('admin.products.show', $product) }}" class="admin-product-card">
                    @php $thumbUrl = $product->images->first() ? (str_starts_with($product->images->first()->image, 'http') ? $product->images->first()->image : asset('storage/'.$product->images->first()->image)) : null; @endphp
                    @if ($thumbUrl)
                        <img src="{{ $thumbUrl }}" alt="" class="admin-product-image" loading="lazy">
                    @else
                        <span class="admin-product-fallback">{{ strtoupper(substr($product->name, 0, 1)) }}</span>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">{{ $product->name }}</p>
                        <p class="text-xs text-neutral-400">${{ number_format($product->price, 2) }} · {{ $product->status }}</p>
                    </div>
                </a>
            @empty
                <p class="col-span-full py-8 text-center text-sm text-neutral-400">No listings yet</p>
            @endforelse
        </div>
    </div>
@endsection