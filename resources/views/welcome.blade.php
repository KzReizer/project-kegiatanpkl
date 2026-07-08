<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jurnal PKL</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <script>
        const storedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.add(storedTheme || (prefersDark ? 'dark' : 'light'));
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <main class="app-shell welcome-shell">
        <div class="welcome-inner">
            <section class="welcome-panel">
                <div>
                    <p class="eyebrow">Jurnal PKL</p>
                    <h1>Catatan internship yang rapi dan siap dilaporkan.</h1>
                    <p>Kelola kegiatan harian, unggah dokumentasi, filter riwayat, dan cetak laporan PKL dari satu dashboard modern.</p>

                    <div class="welcome-actions">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="primary-button">
                                <i data-lucide="log-in"></i>
                                Masuk
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="secondary-button">
                                <i data-lucide="user-plus"></i>
                                Daftar
                            </a>
                        @endif
                        <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti tema">
                            <span data-theme-icon><i data-lucide="moon"></i></span>
                        </button>
                    </div>
                </div>

                <div class="welcome-preview" aria-label="Preview dashboard">
                    <div class="preview-row">
                        <span class="card-icon"><i data-lucide="calendar-check"></i></span>
                        <div>
                            <strong>12 catatan bulan ini</strong>
                            <span>Aktivitas tersusun per timeline.</span>
                        </div>
                    </div>
                    <div class="preview-row">
                        <span class="card-icon"><i data-lucide="image"></i></span>
                        <div>
                            <strong>Dokumentasi visual</strong>
                            <span>Upload multi foto dengan preview.</span>
                        </div>
                    </div>
                    <div class="preview-row">
                        <span class="card-icon"><i data-lucide="download"></i></span>
                        <div>
                            <strong>Export dan cetak</strong>
                            <span>Laporan siap dibagikan.</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
