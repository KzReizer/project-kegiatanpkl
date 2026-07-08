@extends('layouts.app', ['title' => 'Admin Laporan PKL'])

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Panel admin</p>
            <h1>Semua akun PKL</h1>
            <p class="subtle-text">Pantau progres laporan, dokumentasi, dan status arsip dari setiap akun peserta.</p>
        </div>

        <div class="summary-grid">
            <article class="summary-item animate-rise">
                <span class="summary-top">
                    <span>
                        <span class="summary-value" data-counter="{{ $totalUsers }}">{{ $totalUsers }}</span>
                        <span class="summary-label">Akun</span>
                    </span>
                    <span class="summary-icon"><i data-lucide="users-round"></i></span>
                </span>
            </article>
            <article class="summary-item animate-rise delay-1">
                <span class="summary-top">
                    <span>
                        <span class="summary-value" data-counter="{{ $totalJournals }}">{{ $totalJournals }}</span>
                        <span class="summary-label">Laporan</span>
                    </span>
                    <span class="summary-icon"><i data-lucide="files"></i></span>
                </span>
            </article>
            <article class="summary-item animate-rise delay-2">
                <span class="summary-top">
                    <span>
                        <span class="summary-value" data-counter="{{ $totalPhotos }}">{{ $totalPhotos }}</span>
                        <span class="summary-label">Foto</span>
                    </span>
                    <span class="summary-icon"><i data-lucide="image"></i></span>
                </span>
            </article>
        </div>
    </section>

    <section class="admin-grid">
        @forelse ($users as $user)
            <a class="account-card reveal-on-scroll" href="{{ route('admin.users.show', $user) }}">
                <div class="account-avatar">{{ \Illuminate\Support\Str::of($user->name)->substr(0, 1)->upper() }}</div>
                <div class="account-main">
                    <div class="account-title">
                        <h2>{{ $user->name }}</h2>
                        <span>{{ $user->role }}</span>
                    </div>
                    <p>{{ $user->email }}</p>
                    <dl class="account-stats">
                        <div>
                            <dt>Total</dt>
                            <dd>{{ $user->journals_count }}</dd>
                        </div>
                        <div>
                            <dt>Aktif</dt>
                            <dd>{{ $user->active_journals_count }}</dd>
                        </div>
                        <div>
                            <dt>Arsip</dt>
                            <dd>{{ $user->archived_journals_count }}</dd>
                        </div>
                    </dl>
                    <small>
                        Terakhir:
                        {{ $user->journals_max_activity_date ? \Illuminate\Support\Carbon::parse($user->journals_max_activity_date)->translatedFormat('d F Y') : 'Belum ada laporan' }}
                    </small>
                </div>
            </a>
        @empty
            <div class="empty-state">
                <span class="empty-illustration"><i data-lucide="users-round"></i></span>
                <h3>Belum ada akun</h3>
            </div>
        @endforelse
    </section>
@endsection
