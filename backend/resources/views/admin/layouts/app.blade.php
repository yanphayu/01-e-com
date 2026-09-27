<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function () {
            var theme = 'light';

            try {
                var stored = window.localStorage.getItem('trinity-theme');
                theme = stored === 'light' || stored === 'dark'
                    ? stored
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            } catch (error) {
                theme = 'light';
            }

            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <script>window.AdminUser = { id: {{ auth()->id() }} };</script>
    <title>@yield('title', 'Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="admin-body antialiased">
    <div class="admin-shell">
        <div class="admin-sidebar-overlay" data-sidebar-overlay></div>

        <aside id="admin-sidebar" class="admin-sidebar" data-admin-sidebar aria-label="Admin navigation" aria-hidden="false">
            <div class="admin-brand">
                <svg class="admin-brand-mark" viewBox="138.8 116 322.4 283" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="TRINITY">
                    <path d="M 300 130 L 259.5 276.6 L 152.8 385 L 300 346.8 L 447.2 385 L 340.5 276.6 Z" stroke="currentColor" stroke-width="26" stroke-linejoin="miter" stroke-miterlimit="10"></path>
                    <path d="M 259.5 276.6 L 300 346.8 L 340.5 276.6 Z" fill="currentColor"></path>
                    <circle cx="300" cy="130" r="9" fill="currentColor"></circle>
                    <circle cx="152.8" cy="385" r="9" fill="currentColor"></circle>
                    <circle cx="447.2" cy="385" r="9" fill="currentColor"></circle>
                </svg>
                <div>
                    <p class="admin-brand-name">TRINITY</p>
                </div>
            </div>

            <nav class="admin-nav">
                @php
                    $items = [
                        'Dashboard' => ['admin.dashboard', [], 'dashboard'],
                        'Analytics' => ['admin.analytics', [], 'analytics'],
                        'Products' => ['admin.products.index', ['index', 'show'], 'products'],
                        'Reports' => ['admin.reports.index', [], 'reports'],
                        'Ads' => ['admin.ads.index', ['index', 'create', 'edit'], 'ads'],
                        'Users' => ['admin.users.index', ['index', 'show'], 'users'],
                        'Catalog' => null,
                        'Categories' => ['admin.categories.index', [], 'categories'],
                        'Subcategories' => ['admin.subcategories.index', [], 'subcategories'],
                        'Brands' => ['admin.brands.index', [], 'brands'],
                        'Models' => ['admin.models.index', [], 'models'],
                        'Attributes' => ['admin.attributes.index', [], 'attributes'],
                        'Settings' => ['admin.settings.auth-panel', [], 'settings'],
                    ];
                    $icons = [
                        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>',
                        'analytics' => '<path d="M3 3v18h18"/><path d="M7.5 16.5v-4"/><path d="M12 16.5v-8"/><path d="M16.5 16.5v-6"/>',
                        'products' => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
                        'reports' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M12 11v4"/><path d="M12 18h.01"/>',
                        'ads' => '<rect x="2" y="4" width="20" height="14" rx="2"/><path d="M2 9h20"/><path d="M6 14h6"/><path d="M18 14h.01"/>',
                        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                        'categories' => '<path d="M12 2 2 7l10 5 10-5z"/><path d="M2 12l10 5 10-5"/><path d="M2 17l10 5 10-5"/>',
                        'subcategories' => '<path d="M20.59 13.41 12 22l-9-9V3h10l7.59 7.59a2 2 0 0 1 0 2.82z"/><circle cx="7.5" cy="7.5" r="1.2"/>',
                        'brands' => '<circle cx="12" cy="8" r="6"/><path d="M15.5 13.5 17 22l-5-3-5 3 1.5-8.5"/>',
                        'models' => '<path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="M3 8l9 5 9-5"/><path d="M12 13v8"/>',
                        'attributes' => '<path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M1.5 14h5"/><path d="M9.5 8h5"/><path d="M17.5 16h5"/>',
                        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
                    ];
                    $unreadReports = $appUnreadReportCount ?? 0;
                @endphp

                @foreach ($items as $label => $target)
                    @if ($target === null)
                        <p class="admin-nav-section">{{ $label }}</p>
                    @else
                        @php [$route, $patterns, $icon] = $target; @endphp
                        @php
                            $active = request()->routeIs($route) || collect($patterns)->contains(fn ($p) => request()->routeIs($route.'.'.$p));
                        @endphp
                        <a href="{{ route($route) }}"
                           class="admin-nav-link {{ $active ? 'is-active' : '' }}"
                           @if ($active) aria-current="page" @endif>
                            <svg class="admin-nav-icon"
                                 xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.9"
                                 stroke-linecap="round"
                                 stroke-linejoin="round"
                                 aria-hidden="true">{!! $icons[$icon] ?? '' !!}</svg>
                            <span>{{ $label }}</span>
                            @if ($route === 'admin.reports.index')
                                <span id="admin-report-unread-count"
                                      data-admin-report-unread-count
                                      class="admin-nav-count {{ $unreadReports > 0 ? '' : 'hidden' }}"
                                      aria-label="{{ $unreadReports }} unread reports"
                                      aria-live="polite">{{ $unreadReports }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="admin-sidebar-footer">
                <div class="admin-user-card">
                    @include('admin.partials.avatar', [
                        'avatar' => auth()->user()->profile?->avatar,
                        'name' => auth()->user()->name,
                        'primary' => true,
                        'class' => 'admin-avatar object-cover',
                    ])
                    <div class="min-w-0">
                        <p class="admin-user-name">{{ auth()->user()->name }}</p>
                        <p class="admin-user-email">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="admin-signout">Sign out</button>
                </form>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="admin-header-inner">
                    <div class="admin-header-leading">
                        <button type="button" class="admin-menu-button" data-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false" aria-label="Open navigation">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="admin-breadcrumb">
                            <a href="{{ route('admin.dashboard') }}" class="admin-breadcrumb-link">Admin</a>
                            @hasSection('crumb')
                                <span class="admin-breadcrumb-separator">/</span>
                                <span class="admin-breadcrumb-current">@yield('crumb')</span>
                            @endif
                        </div>
                    </div>

                    <div class="admin-header-actions">
                        <button type="button" class="admin-icon-button" data-theme-toggle aria-label="Switch theme" aria-pressed="false" title="Switch theme">
                            <svg class="theme-icon-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg class="theme-icon-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.001 9.001 0 1020.354 15.354z" />
                            </svg>
                        </button>

                    </div>
                </div>
            </header>

            <div class="admin-content">
                <section class="admin-page">
                    @yield('content')
                </section>
            </div>
        </main>
    </div>

    <div class="admin-toast-host" data-admin-toast-host aria-live="polite">
        @if (session('status'))
            <div class="admin-toast admin-toast--success" data-admin-toast>
                <p class="admin-toast-message">{{ session('status') }}</p>
                <button type="button" class="admin-toast-close" data-admin-toast-close aria-label="Dismiss">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
        @if (session('error'))
            <div class="admin-toast admin-toast--error" data-admin-toast>
                <p class="admin-toast-message">{{ session('error') }}</p>
                <button type="button" class="admin-toast-close" data-admin-toast-close aria-label="Dismiss">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
</body>
</html>
