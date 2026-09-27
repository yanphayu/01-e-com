@extends('admin.layouts.app')

@section('title', 'Sign-in panel content')

@section('crumb')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-neutral-900 hover:underline">Settings</a>
    <span class="mx-2 text-neutral-300">/</span>Sign-in panel
@endsection

@section('content')
    <div class="admin-page-heading">
        <div>
            <h2 class="admin-page-title">Sign-in panel content</h2>
            <p class="admin-page-subtitle">
                This copy fills the left panel on every storefront auth page: login, register, forgot password,
                reset password, and email verification.
            </p>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
        <form method="POST" action="{{ route('admin.settings.auth-panel.update') }}" class="admin-form">
            @csrf
            @method('PUT')

            @php
                $fields = [
                    'headline_1' => ['Headline line 1', 'input', 'A calmer way'],
                    'headline_2' => ['Headline line 2', 'input', 'to shop.'],
                    'sub' => ['Description', 'textarea', 'Join thousands discovering thoughtfully made products.'],
                    'bullet_1' => ['Highlight 1', 'input', 'Curated, quality-first collections'],
                    'bullet_2' => ['Highlight 2', 'input', 'Fast, secure checkout'],
                    'bullet_3' => ['Highlight 3', 'input', 'Free returns within 30 days'],
                    'footer' => ['Footer line', 'input', '© {year} TRINITY. All rights reserved.'],
                ];
            @endphp

            @foreach ($fields as $key => [$label, $type, $placeholder])
                <div>
                    <label for="{{ $key }}" class="admin-label">{{ $label }}</label>

                    @if ($type === 'textarea')
                        <textarea id="{{ $key }}"
                                  name="{{ $key }}"
                                  rows="3"
                                  class="admin-control admin-textarea"
                                  placeholder="{{ $placeholder }}">{{ old($key, $content[$key]) }}</textarea>
                    @else
                        <input type="text"
                               id="{{ $key }}"
                               name="{{ $key }}"
                               value="{{ old($key, $content[$key]) }}"
                               placeholder="{{ $placeholder }}"
                               class="admin-control">
                    @endif

                    @error($key)
                        <p class="admin-field-error">{{ $message }}</p>
                    @enderror

                    <p class="mt-1 text-xs text-neutral-500">Default: {{ $defaults[$key] }}</p>
                </div>
            @endforeach

            <p class="text-xs text-neutral-500">
                Leave a field empty to fall back to its default. Use <code>{year}</code> in the footer line to
                insert the current year automatically.
            </p>

            <div class="admin-form-actions">
                <button type="submit" class="admin-button admin-button-primary">Save content</button>
                <button type="submit"
                        name="reset"
                        value="1"
                        class="admin-button admin-button-secondary">Restore defaults</button>
            </div>
        </form>

        <div class="admin-card admin-card--padded">
            <h3 class="text-sm font-semibold text-neutral-900">Current preview</h3>

            <p class="mt-3 text-sm font-semibold leading-snug text-neutral-900">
                {{ $content['headline_1'] }}<br>{{ $content['headline_2'] }}
            </p>
            <p class="mt-2 text-sm text-neutral-600">{{ $content['sub'] }}</p>
            <ul class="mt-3 space-y-1.5 text-sm text-neutral-700">
                @foreach (['bullet_1', 'bullet_2', 'bullet_3'] as $key)
                    <li class="flex items-start gap-2">
                        <span aria-hidden="true">&#10003;</span>
                        <span>{{ $content[$key] }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-4 border-t border-neutral-200 pt-3 text-xs text-neutral-500">
                {{ str_replace('{year}', now()->year, $content['footer']) }}
            </p>
        </div>
    </div>
@endsection
