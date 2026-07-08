@php
    $primaryPhoto = $journal->photos->first();
    $photoCount = $journal->photos->count();
@endphp

<article class="journal-card {{ $journal->archived_at ? 'is-archived' : '' }}">
    <div class="photo-stack">
        @if ($primaryPhoto)
            @php($photoUrl = asset('storage/'.$primaryPhoto->path))
            <button class="photo-link" type="button" data-photo-src="{{ $photoUrl }}" data-photo-alt="Dokumentasi {{ $journal->title }}">
                <img src="{{ $photoUrl }}" alt="Dokumentasi {{ $journal->title }}">
                @if ($photoCount > 1)
                    <span>{{ $photoCount }} foto</span>
                @endif
            </button>
        @else
            <div class="photo-placeholder" aria-hidden="true">
                <span><i data-lucide="image-off"></i></span>
            </div>
        @endif
    </div>

    <div class="journal-content">
        <div class="journal-meta">
            <time datetime="{{ $journal->activity_date->toDateString() }}">
                <i data-lucide="calendar-days"></i>
                {{ $journal->activity_date->locale('id')->translatedFormat('d F Y') }}
            </time>
            @if ($journal->location)
                <span><i data-lucide="map-pin"></i> {{ $journal->location }}</span>
            @endif
            <span><i data-lucide="tag"></i> {{ $journal->category }}</span>
            @if ($journal->archived_at)
                <span class="status-pill is-archived"><i data-lucide="archive"></i> Arsip</span>
            @else
                <span class="status-pill"><i data-lucide="activity"></i> Aktif</span>
            @endif
        </div>

        <h3>{{ $journal->title }}</h3>
        <p class="journal-summary">{{ $journal->description }}</p>

        <details class="journal-details">
            <summary>Detail</summary>
            <div class="detail-body">
                <p>{{ $journal->description }}</p>

                <div class="note-grid">
                    @if ($journal->learning)
                        <section>
                            <strong>Hasil</strong>
                            <p>{{ $journal->learning }}</p>
                        </section>
                    @endif
                    @if ($journal->obstacle)
                        <section>
                            <strong>Kendala</strong>
                            <p>{{ $journal->obstacle }}</p>
                        </section>
                    @endif
                    @if ($journal->next_plan)
                        <section>
                            <strong>Rencana</strong>
                            <p>{{ $journal->next_plan }}</p>
                        </section>
                    @endif
                </div>

                @if ($photoCount > 1)
                    <div class="detail-photo-grid">
                        @foreach ($journal->photos as $photo)
                            @php($photoUrl = asset('storage/'.$photo->path))
                            <button type="button" data-photo-src="{{ $photoUrl }}" data-photo-alt="Dokumentasi {{ $journal->title }}">
                                <img src="{{ $photoUrl }}" alt="Dokumentasi {{ $journal->title }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </details>

        @if ($showActions ?? true)
            <div class="card-actions">
                <a class="secondary-button compact-button" href="{{ route('journals.edit', $journal) }}">
                    <i data-lucide="pencil-line"></i>
                    Edit
                </a>
                <form action="{{ route('journals.duplicate', $journal) }}" method="POST">
                    @csrf
                    <button class="secondary-button compact-button" type="submit">
                        <i data-lucide="copy"></i>
                        Duplikat
                    </button>
                </form>
                <form action="{{ route('journals.archive', array_merge(['journal' => $journal], request()->query())) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button class="secondary-button compact-button" type="submit">
                        <i data-lucide="{{ $journal->archived_at ? 'archive-restore' : 'archive' }}"></i>
                        {{ $journal->archived_at ? 'Pulihkan' : 'Arsip' }}
                    </button>
                </form>
                <form action="{{ route('journals.destroy', $journal) }}" method="POST" onsubmit="return confirm('Hapus permanen catatan ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="danger-button compact-button" type="submit">
                        <i data-lucide="trash-2"></i>
                        Hapus
                    </button>
                </form>
            </div>
        @endif
    </div>
</article>
