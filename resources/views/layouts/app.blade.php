<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

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
        <div class="app-shell">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="page-heading">
                    <div class="page-heading-inner">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main @class(['app-main', 'content-shell' => ! isset($slot)])>
                @if (isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>

        @if (session('status'))
            <div class="toast-stack" aria-live="polite">
                <div class="toast" data-toast>
                    <span class="toast-icon"><i data-lucide="check"></i></span>
                    <div>
                        <strong>Berhasil</strong>
                        <p>{{ session('status') }}</p>
                    </div>
                    <button class="toast-close" type="button" data-toast-close aria-label="Tutup notifikasi">
                        <i data-lucide="x"></i>
                    </button>
                </div>
            </div>
        @endif

        <div class="photo-modal" data-photo-modal hidden>
            <button class="photo-modal-close" type="button" data-photo-close aria-label="Tutup preview foto">
                <i data-lucide="x"></i>
                Tutup
            </button>
            <img src="" alt="Preview dokumentasi" data-photo-modal-image>
        </div>
    </body>
</html>
