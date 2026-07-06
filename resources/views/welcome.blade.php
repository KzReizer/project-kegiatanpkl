<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jurnal PKL</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-6 py-16">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-700">Laravel Breeze</p>
            <h1 class="mt-4 text-4xl font-semibold">Selamat datang di aplikasi jurnal PKL</h1>
            <p class="mt-4 max-w-2xl text-lg text-slate-600">Masuk untuk mulai mencatat kegiatan, mengunggah dokumentasi, dan mengelola jurnal harian Anda.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="rounded-full bg-emerald-700 px-5 py-3 font-semibold text-white">Masuk</a>
                @endif
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="rounded-full border border-slate-300 px-5 py-3 font-semibold text-slate-700">Daftar</a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
