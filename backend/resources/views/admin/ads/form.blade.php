@extends('admin.layouts.app')

@section('title', isset($ad) ? 'Edit ad' : 'New ad')

@section('crumb')
    <a href="{{ route('admin.ads.index') }}" class="hover:text-neutral-900 hover:underline">Ads</a>
    <span class="mx-2 text-neutral-300">/</span>{{ isset($ad) ? 'Edit' : 'New' }}
@endsection

@section('content')
    <div class="max-w-2xl">
        <h2 class="admin-page-title">{{ isset($ad) ? 'Edit ad' : 'New ad' }}</h2>

        <form method="POST"
              action="{{ isset($ad) ? route('admin.ads.update', $ad) : route('admin.ads.store') }}"
              enctype="multipart/form-data"
              class="admin-form">
            @csrf
            @if (isset($ad))
                @method('PUT')
            @endif

            <div>
                <label for="headline" class="admin-label">Headline <span class="font-normal text-neutral-400">(optional)</span></label>
                <input type="text"
                       id="headline"
                       name="headline"
                       value="{{ old('headline', $ad->headline ?? '') }}"
                       class="admin-control">
                @error('headline')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="subtext" class="admin-label">Sub text</label>
                <textarea id="subtext"
                          name="subtext"
                          rows="2"
                          class="admin-control admin-textarea">{{ old('subtext', $ad->subtext ?? '') }}</textarea>
                @error('subtext')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="button_label" class="admin-label">Button label</label>
                    <input type="text"
                           id="button_label"
                           name="button_label"
                           value="{{ old('button_label', $ad->button_label ?? '') }}"
                           placeholder="Shop now"
                           class="admin-control">
                    @error('button_label')
                        <p class="admin-field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="link_url" class="admin-label">Link URL</label>
                    <input type="text"
                           id="link_url"
                           name="link_url"
                           value="{{ old('link_url', $ad->link_url ?? '') }}"
                           placeholder="/products?category_id=1"
                           class="admin-control">
                    @error('link_url')
                        <p class="admin-field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="image" class="admin-label">
                    Image {{ isset($ad) ? '(leave empty to keep the current image)' : '' }}
                </label>

                @if (isset($ad) && $ad->image)
                    <img src="{{ asset('storage/'.$ad->image) }}"
                         alt=""
                         class="mb-2 h-32 w-full rounded-lg border border-neutral-200 object-cover">
                @endif

                <input type="file"
                       id="image"
                       name="image"
                       accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                       @required(! isset($ad))
                       class="admin-control">
                @error('image')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="sort_order" class="admin-label">Order</label>
                <input type="number"
                       id="sort_order"
                       name="sort_order"
                       min="0"
                       value="{{ old('sort_order', $ad->sort_order ?? '') }}"
                       placeholder="Auto"
                       class="admin-control">
                @error('sort_order')
                    <p class="admin-field-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-neutral-700">
                <input type="checkbox"
                       name="is_active"
                       value="1"
                       @checked(old('is_active', $ad->is_active ?? true))
                       class="admin-checkbox">
                Show this ad on the storefront
            </label>

            <div class="admin-form-actions">
                <button type="submit" class="admin-button admin-button-primary">
                    {{ isset($ad) ? 'Save changes' : 'Create ad' }}
                </button>
                <a href="{{ route('admin.ads.index') }}" class="admin-button admin-button-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
