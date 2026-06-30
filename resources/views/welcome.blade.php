<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Jurnal PKL</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="app-shell">
        <section class="topbar" aria-label="Ringkasan jurnal PKL">
            <div>
                <p class="eyebrow">Jurnal kegiatan harian</p>
                <h1>PKL Logbook</h1>
                <p class="lede">Simpan dokumentasi foto dan catatan kegiatan harian supaya bahan laporan PKL tidak tercecer.</p>
            </div>

            <div class="summary-grid">
                <article class="summary-item">
                    <span class="summary-value">{{ $totalDays }}</span>
                    <span class="summary-label">Hari tercatat</span>
                </article>
                <article class="summary-item">
                    <span class="summary-value">{{ $journals->count() }}</span>
                    <span class="summary-label">Catatan</span>
                </article>
                <article class="summary-item">
                    <span class="summary-value">{{ $totalPhotos }}</span>
                    <span class="summary-label">Foto</span>
                </article>
            </div>
        </section>

        @if (session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif

        <section class="workspace">
            <form class="entry-form" action="{{ route('journals.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="section-heading">
                    <p class="eyebrow">Input kegiatan</p>
                    <h2>Catatan hari ini</h2>
                </div>

                <label class="field">
                    <span>Tanggal</span>
                    <input type="date" name="activity_date" value="{{ old('activity_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                    @error('activity_date')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="field">
                    <span>Judul kegiatan</span>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="Contoh: Membuat halaman dashboard" maxlength="120" required>
                    @error('title')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="field">
                    <span>Catatan</span>
                    <textarea name="description" rows="7" placeholder="Tulis pekerjaan yang dilakukan, kendala, hasil, atau hal yang dipelajari." required>{{ old('description') }}</textarea>
                    @error('description')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="file-field">
                    <span>Foto dokumentasi</span>
                    <input id="photo-input" type="file" name="photo" accept="image/*">
                    <strong>Pilih foto</strong>
                    <em id="photo-name">Belum ada foto dipilih</em>
                    @error('photo')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <button class="primary-button" type="submit">Simpan catatan</button>
            </form>

            <section class="journal-panel" aria-label="Daftar catatan PKL">
                <div class="panel-header">
                    <div>
                        <p class="eyebrow">Riwayat</p>
                        <h2>Dokumentasi tersimpan</h2>
                    </div>

                    @if ($latestJournal)
                        <p class="latest-date">
                            Terakhir: {{ $latestJournal->activity_date->locale('id')->translatedFormat('d F Y') }}
                        </p>
                    @endif
                </div>

                @forelse ($journals as $journal)
                    <article class="journal-card">
                        @if ($journal->photo_path)
                            @php($photoUrl = asset('storage/'.$journal->photo_path))
                            <a class="photo-link" href="{{ $photoUrl }}" target="_blank" rel="noreferrer">
                                <img src="{{ $photoUrl }}" alt="Dokumentasi {{ $journal->title }}">
                            </a>
                        @else
                            <div class="photo-placeholder" aria-hidden="true">
                                <span>DOC</span>
                            </div>
                        @endif

                        <div class="journal-content">
                            <div class="journal-meta">
                                <time datetime="{{ $journal->activity_date->toDateString() }}">
                                    {{ $journal->activity_date->locale('id')->translatedFormat('l, d F Y') }}
                                </time>
                                @if ($journal->photo_original_name)
                                    <span>{{ $journal->photo_original_name }}</span>
                                @endif
                            </div>

                            <h3>{{ $journal->title }}</h3>
                            <p>{{ $journal->description }}</p>

                            <form action="{{ route('journals.destroy', $journal) }}" method="POST" onsubmit="return confirm('Hapus catatan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-button" type="submit">Hapus</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <span>LOG</span>
                        <h3>Belum ada catatan PKL</h3>
                        <p>Mulai dari kegiatan hari ini. Setelah disimpan, catatan dan foto akan muncul di sini.</p>
                    </div>
                @endforelse
            </section>
        </section>
    </main>
</body>
</html>
