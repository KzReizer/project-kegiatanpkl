<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <script>
            const storedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.add(storedTheme || (prefersDark ? 'dark' : 'light'));
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="app-shell auth-shell">
            <div class="auth-wrap">
                <div class="auth-logo">
                    <a href="/" aria-label="Beranda Jurnal PKL">
                        <i data-lucide="notebook-tabs"></i>
                    </a>
                </div>

                <div class="auth-card">
                    <div class="auth-copy">
                        <p class="eyebrow">Jurnal PKL</p>
                        <h1>Workspace Jurnal PKL</h1>
                        <p>Kelola catatan harian, dokumentasi, dan laporan PKL dengan rapi.</p>
                    </div>

                    {{ $slot }}
                </div>

                <div class="auth-actions" style="justify-content: center; margin-top: 0;">
                    <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema">
                        <span data-theme-icon><i data-lucide="moon"></i></span>
                    </button>
                </div>
            </div>
        </div>
    </body>
</html>
