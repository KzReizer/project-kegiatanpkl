@extends('layouts.app', ['title' => 'Jurnal PKL'])

@section('content')
    @php
        $hasAdvancedFilters = filled($filters['month'])
            || filled($filters['day'])
            || filled($filters['category'])
            || filled($filters['photo'])
            || $filters['sort'] !== 'newest'
            || $filters['status'] !== 'active';
    @endphp

    <section class="page-header">
        <div>
            <p class="eyebrow">Jurnal kegiatan harian</p>
            <h1>Dokumentasi PKL</h1>
            <p class="subtle-text">Catat progres harian, simpan dokumentasi, dan susun laporan PKL dalam satu workspace yang rapi.</p>
        </div>

        <div class="summary-grid">
            <a class="summary-item" href="{{ route('journals.index', ['day' => now()->toDateString()]) }}">
                <span class="summary-top">
                    <span>
                        <span class="summary-value" data-counter="{{ $stats['today'] }}">{{ $stats['today'] }}</span>
                        <span class="summary-label">Hari ini</span>
                    </span>
                    <span class="summary-icon"><i data-lucide="calendar-check"></i></span>
                </span>
            </a>
            <a class="summary-item" href="{{ route('journals.index', ['photo' => 'with']) }}">
                <span class="summary-top">
                    <span>
                        <span class="summary-value" data-counter="{{ $stats['photos'] }}">{{ $stats['photos'] }}</span>
                        <span class="summary-label">Foto</span>
                    </span>
                    <span class="summary-icon"><i data-lucide="image"></i></span>
                </span>
            </a>
            <a class="summary-item" href="{{ route('journals.index', ['month' => now()->format('Y-m')]) }}">
                <span class="summary-top">
                    <span>
                        <span class="summary-value" data-counter="{{ $stats['this_month'] }}">{{ $stats['this_month'] }}</span>
                        <span class="summary-label">Bulan ini</span>
                    </span>
                    <span class="summary-icon"><i data-lucide="trending-up"></i></span>
                </span>
            </a>
            <a class="summary-item" href="{{ route('journals.index', ['status' => 'archived']) }}">
                <span class="summary-top">
                    <span>
                        <span class="summary-value" data-counter="{{ $stats['archived'] }}">{{ $stats['archived'] }}</span>
                        <span class="summary-label">Arsip</span>
                    </span>
                    <span class="summary-icon"><i data-lucide="archive"></i></span>
                </span>
            </a>
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
                        <i data-lucide="clock-3"></i>
                        Terakhir: {{ $latestJournal->activity_date->locale('id')->translatedFormat('d F Y') }}
                    </p>
                @endif
            </div>

            <form class="filter-bar" action="{{ route('journals.index') }}" method="GET" data-realtime-filter>
                <div class="filter-primary">
                    <label class="field search-field">
                        <span>Cari realtime</span>
                        <span class="field-control-wrap">
                            <i data-lucide="search"></i>
                            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Judul, catatan, tempat">
                        </span>
                    </label>

                    <label class="field filter-mini">
                        <span>Sort</span>
                        <select name="sort">
                            <option value="newest" @selected($filters['sort'] === 'newest')>Terbaru</option>
                            <option value="oldest" @selected($filters['sort'] === 'oldest')>Terlama</option>
                            <option value="title" @selected($filters['sort'] === 'title')>Judul A-Z</option>
                        </select>
                    </label>

                    <label class="field filter-mini">
                        <span>Status</span>
                        <select name="status">
                            <option value="active" @selected($filters['status'] === 'active')>Aktif</option>
                            <option value="archived" @selected($filters['status'] === 'archived')>Arsip</option>
                            <option value="all" @selected($filters['status'] === 'all')>Semua</option>
                        </select>
                    </label>

                    <div class="filter-actions">
                        <button class="primary-button compact-button" type="submit">
                            <i data-lucide="search"></i>
                            Cari
                        </button>
                        <a class="secondary-button compact-button" href="{{ route('journals.index') }}">
                            <i data-lucide="rotate-ccw"></i>
                            Reset
                        </a>
                        <a class="secondary-button compact-button" href="{{ route('journals.print', request()->query()) }}" target="_blank" rel="noreferrer">
                            <i data-lucide="printer"></i>
                            Cetak
                        </a>
                        <a class="secondary-button compact-button" href="{{ route('journals.export', request()->query()) }}">
                            <i data-lucide="download"></i>
                            Export
                        </a>
                    </div>
                </div>

                <details class="advanced-filters" @if ($hasAdvancedFilters) open @endif>
                    <summary>
                        Filter lanjutan
                        @if ($hasAdvancedFilters)
                            <span>aktif</span>
                        @endif
                    </summary>

                    <div class="advanced-filter-grid">
                        @if (filled($filters['day']))
                            <input type="hidden" name="day" value="{{ $filters['day'] }}">
                            <div class="filter-chip">
                                <i data-lucide="calendar-days"></i>
                                Hari: {{ \Illuminate\Support\Carbon::parse($filters['day'])->translatedFormat('d F Y') }}
                            </div>
                        @endif

                        <label class="field">
                            <span>Bulan</span>
                            <input type="month" name="month" value="{{ $filters['month'] }}">
                        </label>

                        <label class="field">
                            <span>Kategori</span>
                            <select name="category">
                                <option value="">Semua kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="field">
                            <span>Foto</span>
                            <select name="photo">
                                <option value="">Semua foto</option>
                                <option value="with" @selected($filters['photo'] === 'with')>Ada foto</option>
                                <option value="without" @selected($filters['photo'] === 'without')>Tanpa foto</option>
                            </select>
                        </label>
                    </div>
                </details>
            </form>

            <div class="timeline">
                @forelse ($groupedJournals as $month => $days)
                    <section class="timeline-month">
                        <h3>{{ $month }}</h3>

                        @foreach ($days as $day => $dayJournals)
                            <section class="timeline-day">
                                <h4>{{ $day }}</h4>
                                <div class="journal-list">
                                    @foreach ($dayJournals as $journal)
                                        @include('journals._card', ['journal' => $journal])
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </section>
                @empty
                    <div class="empty-state">
                        <span class="empty-illustration"><i data-lucide="notebook-pen"></i></span>
                        <h3>Catatan tidak ditemukan</h3>
                        <p>Ubah kata kunci, filter tanggal, atau status arsip.</p>
                        <a class="primary-button" href="#journal-form">
                            <i data-lucide="plus"></i>
                            Buat catatan pertama
                        </a>
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
