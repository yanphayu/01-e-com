<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <title>TRINITY API</title>
    @vite(['resources/css/app.css'])
</head>
<body class="admin-welcome-page antialiased">
    <main class="admin-welcome-shell">
        <section class="admin-welcome-card">
            <p class="admin-welcome-kicker">TRINITY / API</p>
            <h1 class="admin-welcome-title">TRINITY API</h1>
            <p class="admin-welcome-copy">The web API is available under <span class="admin-api-chip"><code>/api</code></span></p>
        </section>
    </main>
</body>
</html>
