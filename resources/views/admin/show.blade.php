@extends('layouts.app', ['title' => 'Laporan '.$user->name])

@section('content')
    @php
        $hasAdvancedFilters = filled($filters['month'])
            || filled($filters['category'])
            || filled($filters['photo'])
            || $filters['sort'] !== 'newest'
            || $filters['status'] !== 'active';
    @endphp

    <section class="page-header">
        <div>
            <p class="eyebrow">Detail akun</p>
            <h1>{{ $user->name }}</h1>
            <p class="subtle-text">{{ $user->email }} • role {{ $user->role }}</p>
        </div>

        <div class="account-detail-box">
            <strong>{{ $filteredCount }}</strong>
            <span>laporan ditemukan</span>
            <a class="secondary-button compact-button" href="{{ route('admin.users.index') }}">Kembali</a>
        </div>
    </section>

    <section class="journal-panel admin-report-panel">
        <form class="filter-bar" action="{{ route('admin.users.show', $user) }}" method="GET" data-realtime-filter>
            <div class="filter-primary">
                <label class="field search-field">
                    <span>Cari laporan</span>
                    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Judul, catatan, tempat">
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
            </div>

            <details class="advanced-filters" @if ($hasAdvancedFilters) open @endif>
                <summary>
                    Filter lanjutan
                    @if ($hasAdvancedFilters)
                        <span>aktif</span>
                    @endif
                </summary>

                <div class="advanced-filter-grid">
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
                                    @include('journals._card', ['journal' => $journal, 'showActions' => false])
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </section>
            @empty
                <div class="empty-state">
                    <span>LOG</span>
                    <h3>Laporan tidak ditemukan</h3>
                </div>
            @endforelse
        </div>

        @if ($journals->hasPages())
            <div class="pagination-wrap">
                {{ $journals->links() }}
            </div>
        @endif
    </section>
@endsection
