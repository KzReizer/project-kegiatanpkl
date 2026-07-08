@php
    $isEdit = isset($journal);
    $journalPhotos = $isEdit ? $journal->photos : collect();
@endphp

<form id="journal-form" class="entry-form" action="{{ $isEdit ? route('journals.update', $journal) : route('journals.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="section-heading">
        <div>
            <p class="eyebrow">{{ $isEdit ? 'Edit jurnal' : 'Input kegiatan' }}</p>
            <h2>{{ $isEdit ? 'Perbarui catatan' : 'Catatan harian' }}</h2>
        </div>
        <span class="form-icon"><i data-lucide="{{ $isEdit ? 'pencil-line' : 'plus' }}"></i></span>
    </div>

    <details class="form-section" open>
        <summary>Detail kegiatan</summary>
        <div class="form-section-body">
            <div class="form-grid compact">
                <label class="field">
                    <span>Tanggal</span>
                    <input type="date" name="activity_date" value="{{ old('activity_date', optional($journal ?? null)->activity_date?->toDateString() ?? now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                    @error('activity_date')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="field">
                    <span>Kategori</span>
                    <select name="category" required>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(old('category', $journal->category ?? 'Kegiatan') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <small>{{ $message }}</small>
                    @enderror
                </label>
            </div>

            <label class="field">
                <span>Judul kegiatan</span>
                <input type="text" name="title" value="{{ old('title', $journal->title ?? '') }}" placeholder="Contoh: Membuat halaman dashboard" maxlength="120" required>
                @error('title')
                    <small>{{ $message }}</small>
                @enderror
            </label>

            <label class="field">
                <span>Tempat</span>
                <input type="text" name="location" value="{{ old('location', $journal->location ?? '') }}" placeholder="Contoh: Ruang IT" maxlength="120">
                @error('location')
                    <small>{{ $message }}</small>
                @enderror
            </label>

            <label class="field">
                <span>Catatan kegiatan</span>
                <textarea class="auto-grow" name="description" rows="3" required>{{ old('description', $journal->description ?? '') }}</textarea>
                @error('description')
                    <small>{{ $message }}</small>
                @enderror
            </label>
        </div>
    </details>

    <details class="form-section">
        <summary>Hasil & kendala</summary>
        <div class="form-section-body">
            <label class="field">
                <span>Hasil atau pelajaran</span>
                <textarea class="auto-grow" name="learning" rows="2">{{ old('learning', $journal->learning ?? '') }}</textarea>
                @error('learning')
                    <small>{{ $message }}</small>
                @enderror
            </label>

            <label class="field">
                <span>Kendala</span>
                <textarea class="auto-grow" name="obstacle" rows="2">{{ old('obstacle', $journal->obstacle ?? '') }}</textarea>
                @error('obstacle')
                    <small>{{ $message }}</small>
                @enderror
            </label>

            <label class="field">
                <span>Rencana berikutnya</span>
                <textarea class="auto-grow" name="next_plan" rows="2">{{ old('next_plan', $journal->next_plan ?? '') }}</textarea>
                @error('next_plan')
                    <small>{{ $message }}</small>
                @enderror
            </label>
        </div>
    </details>

    <details class="form-section">
        <summary>Dokumentasi</summary>
        <div class="form-section-body">
            @if ($isEdit && $journalPhotos->isNotEmpty())
                <div class="current-photo-grid">
                    @foreach ($journalPhotos as $photo)
                        @php($photoUrl = asset('storage/'.$photo->path))
                        <label class="current-photo">
                            <img src="{{ $photoUrl }}" alt="Dokumentasi {{ $journal->title }}">
                            <span>
                                <input type="checkbox" name="remove_photo_ids[]" value="{{ $photo->id }}">
                                Hapus foto ini
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif

            <label class="file-field dropzone" data-dropzone>
                <span>{{ $isEdit ? 'Tambah foto dokumentasi' : 'Foto dokumentasi' }}</span>
                <input class="photo-input" type="file" name="photos[]" accept="image/*" multiple>
                <strong><i data-lucide="upload-cloud"></i> Pilih atau drop foto</strong>
                <em class="photo-name">Belum ada foto dipilih</em>
                <div class="upload-preview" data-upload-preview></div>
                @error('photos')
                    <small>{{ $message }}</small>
                @enderror
                @error('photos.*')
                    <small>{{ $message }}</small>
                @enderror
            </label>
        </div>
    </details>

    <div class="form-actions">
        <button class="primary-button" type="submit">
            <i data-lucide="{{ $isEdit ? 'save' : 'check' }}"></i>
            {{ $isEdit ? 'Simpan perubahan' : 'Simpan catatan' }}
        </button>
        @if ($isEdit)
            <a class="secondary-button" href="{{ route('journals.index') }}">
                <i data-lucide="x"></i>
                Batal
            </a>
        @endif
    </div>
</form>
