<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Jurnal PKL' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-frame">
        <aside class="sidebar">
            <a class="brand" href="{{ route('journals.index') }}">
                <span>PKL</span>
                <strong>Logbook</strong>
            </a>

            <nav class="side-nav" aria-label="Navigasi utama">
                <a class="{{ request()->routeIs('journals.index') ? 'active' : '' }}" href="{{ route('journals.index') }}">Jurnal</a>
                <a href="{{ route('journals.print', request()->query()) }}" target="_blank" rel="noreferrer">Cetak laporan</a>
                <a href="{{ route('journals.export', request()->query()) }}">Export CSV</a>
            </nav>
        </aside>

        <main class="content-shell">
            @if (session('status'))
                <div class="notice" role="status">{{ session('status') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <div class="photo-modal" data-photo-modal hidden>
        <button class="photo-modal-close" type="button" data-photo-close aria-label="Tutup preview foto">Tutup</button>
        <img src="" alt="Preview dokumentasi" data-photo-modal-image>
    </div>
</body>
</html>
