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
            <span>{{ $journal->category }}</span>
            @if ($journal->location)
                <span>{{ $journal->location }}</span>
            @endif
        </div>

        <h3>{{ $journal->title }}</h3>
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

        <div class="card-actions">
            <a class="secondary-button compact-button" href="{{ route('journals.edit', $journal) }}">Edit</a>
            <form action="{{ route('journals.destroy', $journal) }}" method="POST" onsubmit="return confirm('Hapus catatan ini?')">
                @csrf
                @method('DELETE')
                <button class="danger-button compact-button" type="submit">Hapus</button>
            </form>
        </div>
    </div>
</article>
