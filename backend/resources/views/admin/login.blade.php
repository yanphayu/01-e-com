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
    <title>Admin Login</title>
    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="admin-login-page antialiased">
    <div class="admin-login-shell">
        <div class="admin-login-brand">
            <svg class="admin-brand-mark" viewBox="138.8 116 322.4 283" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="TRINITY">
                <path d="M 300 130 L 259.5 276.6 L 152.8 385 L 300 346.8 L 447.2 385 L 340.5 276.6 Z" stroke="currentColor" stroke-width="26" stroke-linejoin="miter" stroke-miterlimit="10"></path>
                <path d="M 259.5 276.6 L 300 346.8 L 340.5 276.6 Z" fill="currentColor"></path>
                <circle cx="300" cy="130" r="9" fill="currentColor"></circle>
                <circle cx="152.8" cy="385" r="9" fill="currentColor"></circle>
                <circle cx="447.2" cy="385" r="9" fill="currentColor"></circle>
            </svg>
            <h1 class="admin-login-title">TRINITY Admin</h1>
            <p class="admin-login-subtitle">Sign in to manage the marketplace</p>
        </div>

        <div class="admin-login-card">
            @if ($errors->any())
                <div class="admin-alert admin-alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" class="admin-form-stack">
                @csrf

                <div>
                    <label for="email" class="admin-label">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="admin-control">
                </div>

                <div>
                    <label for="password" class="admin-label">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" class="admin-control">
                </div>

                <label class="admin-check-row">
                    <input type="checkbox" name="remember" class="admin-checkbox">
                    Remember me
                </label>

                <button type="submit" class="admin-button admin-button-primary admin-button-block">Sign in</button>
            </form>
        </div>

        <p class="admin-login-footer">TRINITY Marketplace</p>
    </div>
</body>
</html>
