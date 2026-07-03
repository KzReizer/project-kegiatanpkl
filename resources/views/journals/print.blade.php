<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan PKL</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="print-body">
    <main class="print-shell">
        <header class="print-header">
            <div>
                <p class="eyebrow">Laporan PKL</p>
                <h1>Rekap Kegiatan Harian</h1>
            </div>
            <button class="primary-button compact-button no-print" onclick="window.print()" type="button">Print</button>
        </header>

        <section class="print-summary">
            <span>Total catatan: {{ $journals->count() }}</span>
            @if ($filters['month'] ?? null)
                <span>Bulan: {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $filters['month'])->translatedFormat('F Y') }}</span>
            @endif
            @if ($filters['day'] ?? null)
                <span>Hari: {{ \Illuminate\Support\Carbon::parse($filters['day'])->translatedFormat('d F Y') }}</span>
            @endif
        </section>

        @forelse ($journals as $journal)
            <article class="print-entry">
                <div>
                    <time>{{ $journal->activity_date->locale('id')->translatedFormat('l, d F Y') }}</time>
                    <h2>{{ $journal->title }}</h2>
                    <p><strong>Kategori:</strong> {{ $journal->category }}</p>
                    @if ($journal->location)
                        <p><strong>Tempat:</strong> {{ $journal->location }}</p>
                    @endif
                    <p>{{ $journal->description }}</p>
                    @if ($journal->learning)
                        <p><strong>Hasil:</strong> {{ $journal->learning }}</p>
                    @endif
                    @if ($journal->obstacle)
                        <p><strong>Kendala:</strong> {{ $journal->obstacle }}</p>
                    @endif
                    @if ($journal->next_plan)
                        <p><strong>Rencana:</strong> {{ $journal->next_plan }}</p>
                    @endif
                </div>

                @if ($journal->photos->isNotEmpty())
                    <div class="print-photo-grid">
                        @foreach ($journal->photos->take(4) as $photo)
                            <img src="{{ asset('storage/'.$photo->path) }}" alt="Dokumentasi {{ $journal->title }}">
                        @endforeach
                    </div>
                @endif
            </article>
        @empty
            <div class="empty-state">
                <span>LOG</span>
                <h3>Belum ada data laporan</h3>
            </div>
        @endforelse
    </main>
</body>
</html>
