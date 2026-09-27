@extends('admin.layouts.app')

@section('title', $product->name)

@section('crumb')
    <a href="{{ route('admin.products.index') }}" class="hover:text-neutral-900 hover:underline">Products</a>
    <span class="mx-2 text-neutral-300">/</span>{{ str()->limit($product->name, 30) }}
@endsection

@section('content')
    <div class="admin-page-heading">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="admin-page-title">{{ $product->name }}</h2>
                <span class="admin-badge {{ $product->status === 'approved' ? 'admin-badge-success' : ($product->status === 'pending' ? 'admin-badge-warning' : 'admin-badge-danger') }}">
                    {{ ucfirst($product->status) }}
                </span>
                <span class="admin-badge {{ $product->is_active ? 'admin-badge-info' : 'admin-badge-muted' }}">
                    {{ $product->is_active ? 'Visible' : 'Hidden' }}
                </span>
            </div>
            <p class="admin-page-subtitle">
                Listed by <a href="{{ route('admin.users.show', $product->user) }}" class="font-semibold text-neutral-900 hover:underline">{{ $product->user->name ?? 'Deleted user' }}</a>
                · {{ $product->created_at->diffForHumans() }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($product->status === 'pending')
                <form method="POST" action="{{ route('admin.products.approve', $product) }}">
                    @csrf
                    <button type="submit" class="admin-button admin-button-success">Approve & publish</button>
                </form>
                <form method="POST" action="{{ route('admin.products.reject', $product) }}">
                    @csrf
                    <button type="submit" class="admin-button admin-button-danger">Reject</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.products.approve', $product) }}">
                    @csrf
                    <button type="submit" class="admin-button admin-button-success">Approve</button>
                </form>
                <form method="POST" action="{{ route('admin.products.reject', $product) }}">
                    @csrf
                    <button type="submit" class="admin-button admin-button-danger">Reject</button>
                </form>
            @endif

            <form method="POST" action="{{ route('admin.products.toggle-active', $product) }}">
                @csrf
                <button type="submit" class="admin-button admin-button-secondary">
                    {{ $product->is_active ? 'Hide' : 'Show' }}
                </button>
            </form>

            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Delete this product permanently?">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-button admin-button-danger">Delete</button>
            </form>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="admin-card admin-card--padded">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @forelse ($product->images as $image)
                        @php $img = str_starts_with($image->image, 'http') ? $image->image : asset('storage/'.$image->image); @endphp
                        <img src="{{ $img }}" alt="" class="aspect-square w-full rounded-xl object-cover" loading="lazy">
                    @empty
                        <div class="col-span-full flex h-32 items-center justify-center rounded-xl bg-neutral-50 text-sm text-neutral-400">No images</div>
                    @endforelse
                </div>

                <h3 class="admin-section-title mt-6">Description</h3>
                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-neutral-600">{{ $product->description ?: 'No description provided.' }}</p>

                <h3 class="admin-section-title mt-10">Details</h3>
                <dl class="admin-detail-grid mt-3">
                    <div>
                        <dt class="admin-detail-label">Subcategory</dt>
                        <dd class="admin-detail-value">{{ $product->subcategory->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="admin-detail-label">Category</dt>
                        <dd class="admin-detail-value">{{ $product->subcategory->category->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="admin-detail-label">Brand</dt>
                        <dd class="admin-detail-value">{{ $product->detail->brand->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="admin-detail-label">Model</dt>
                        <dd class="admin-detail-value">{{ $product->detail->model->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="admin-detail-label">Condition</dt>
                        <dd class="admin-detail-value">{{ $product->detail->condition ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="admin-detail-label">Contacts</dt>
                        <dd class="admin-detail-value">{{ $product->phones->pluck('phone')->filter()->implode(', ') ?: '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="admin-card admin-card--padded">
                <h3 class="admin-section-title">Attributes</h3>
                <dl class="admin-detail-grid mt-3">
                    @forelse ($product->productAttributes as $pa)
                        <div>
                            <dt class="admin-detail-label">{{ $pa->attribute->name ?? 'Attribute' }}</dt>
                            <dd class="admin-detail-value">{{ $pa->value }}</dd>
                        </div>
                    @empty
                        <p class="col-span-full text-sm text-neutral-400">No attributes specified.</p>
                    @endforelse
                </dl>
            </div>

            <div class="admin-card admin-card--padded">
                <h3 class="admin-section-title">Comments ({{ $product->comments->count() }})</h3>
                <ul class="mt-3 divide-y divide-neutral-100">
                    @forelse ($product->comments as $comment)
                        <li class="py-3">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="font-semibold text-neutral-900">{{ $comment->user->name ?? 'Deleted user' }}</span>
                                <span class="text-xs text-neutral-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mt-1 text-sm text-neutral-600">{{ $comment->body }}</p>
                        </li>
                    @empty
                        <p class="py-4 text-sm text-neutral-400">No comments yet.</p>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="space-y-4">
            <div class="admin-card admin-card--padded">
                <p class="admin-detail-label">Price</p>
                <p class="mt-2 text-3xl font-bold tracking-tight">${{ number_format($product->price, 2) }}</p>
            </div>

            <div class="admin-card admin-card--padded">
                <h3 class="admin-section-title">Seller</h3>
                <a href="{{ route('admin.users.show', $product->user) }}" class="mt-3 flex items-center gap-3">
                    <span class="admin-avatar admin-avatar--primary">
                        {{ strtoupper(substr($product->user->name ?? 'U', 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">{{ $product->user->name ?? 'Deleted user' }}</p>
                        <p class="truncate text-xs text-neutral-400">{{ $product->user->email ?? '' }}</p>
                    </div>
                </a>
                <p class="mt-3 text-xs text-neutral-400">Joined {{ optional($product->user->created_at)->diffForHumans() }}</p>
            </div>

            @if ($product->detail && ($product->detail->address || $product->detail->province))
                <div class="admin-card admin-card--padded">
                    <h3 class="admin-section-title">Location</h3>
                    <p class="mt-2 text-sm text-neutral-600">
                        {{ collect([$product->detail->address, $product->detail->province, $product->detail->khan, $product->detail->sangkat])->filter()->implode(', ') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection