@extends('layouts.app', ['title' => 'Admin Laporan PKL'])

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Panel admin</p>
            <h1>Semua akun PKL</h1>
        </div>

        <div class="summary-grid">
            <article class="summary-item animate-rise">
                <span class="summary-value">{{ $totalUsers }}</span>
                <span class="summary-label">Akun</span>
            </article>
            <article class="summary-item animate-rise delay-1">
                <span class="summary-value">{{ $totalJournals }}</span>
                <span class="summary-label">Laporan</span>
            </article>
            <article class="summary-item animate-rise delay-2">
                <span class="summary-value">{{ $totalPhotos }}</span>
                <span class="summary-label">Foto</span>
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
                <span>ADM</span>
                <h3>Belum ada akun</h3>
            </div>
        @endforelse
    </section>
@endsection
