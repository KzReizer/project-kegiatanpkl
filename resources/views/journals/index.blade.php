@extends('layouts.app', ['title' => 'Jurnal PKL'])

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Jurnal kegiatan harian</p>
            <h1>Dokumentasi PKL</h1>
        </div>

        <div class="summary-grid">
            <article class="summary-item">
                <span class="summary-value">{{ $stats['days'] }}</span>
                <span class="summary-label">Hari</span>
            </article>
            <article class="summary-item">
                <span class="summary-value">{{ $stats['entries'] }}</span>
                <span class="summary-label">Catatan</span>
            </article>
            <article class="summary-item">
                <span class="summary-value">{{ $stats['photos'] }}</span>
                <span class="summary-label">Foto</span>
            </article>
            <article class="summary-item">
                <span class="summary-value">{{ $stats['this_month'] }}</span>
                <span class="summary-label">Bulan ini</span>
            </article>
        </div>
    </section>

    <section class="workspace">
        @include('journals._form')

        <section class="journal-panel" aria-label="Daftar catatan PKL">
            <div class="panel-header">
                <div>
                    <p class="eyebrow">Riwayat</p>
                    <h2>{{ $filteredCount }} catatan ditemukan</h2>
                </div>

                @if ($latestJournal)
                    <p class="latest-date">
                        Terakhir: {{ $latestJournal->activity_date->locale('id')->translatedFormat('d F Y') }}
                    </p>
                @endif
            </div>

            <form class="filter-bar" action="{{ route('journals.index') }}" method="GET">
                <label class="field search-field">
                    <span>Cari</span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Judul, catatan, tempat">
                </label>

                <label class="field">
                    <span>Dari</span>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                </label>

                <label class="field">
                    <span>Sampai</span>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                </label>

                <label class="field">
                    <span>Kategori</span>
                    <select name="category">
                        <option value="">Semua</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="field">
                    <span>Foto</span>
                    <select name="photo">
                        <option value="">Semua</option>
                        <option value="with" @selected(($filters['photo'] ?? '') === 'with')>Ada foto</option>
                        <option value="without" @selected(($filters['photo'] ?? '') === 'without')>Tanpa foto</option>
                    </select>
                </label>

                <div class="filter-actions">
                    <button class="primary-button compact-button" type="submit">Cari</button>
                    <a class="secondary-button compact-button" href="{{ route('journals.index') }}">Reset</a>
                    <a class="secondary-button compact-button" href="{{ route('journals.print', request()->query()) }}" target="_blank" rel="noreferrer">Cetak</a>
                </div>
            </form>

            <div class="journal-list">
                @forelse ($journals as $journal)
                    @include('journals._card', ['journal' => $journal])
                @empty
                    <div class="empty-state">
                        <span>LOG</span>
                        <h3>Catatan tidak ditemukan</h3>
                        <p>Ubah kata kunci atau filter tanggal.</p>
                    </div>
                @endforelse
            </div>

            @if ($journals->hasPages())
                <div class="pagination-wrap">
                    {{ $journals->links() }}
                </div>
            @endif
        </section>
    </section>
@endsection
