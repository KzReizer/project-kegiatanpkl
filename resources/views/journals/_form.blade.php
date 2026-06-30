@php
    $isEdit = isset($journal);
@endphp

<form class="entry-form" action="{{ $isEdit ? route('journals.update', $journal) : route('journals.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="section-heading">
        <p class="eyebrow">{{ $isEdit ? 'Edit jurnal' : 'Input kegiatan' }}</p>
        <h2>{{ $isEdit ? 'Perbarui catatan' : 'Catatan harian' }}</h2>
    </div>

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
        <textarea name="description" rows="6" required>{{ old('description', $journal->description ?? '') }}</textarea>
        @error('description')
            <small>{{ $message }}</small>
        @enderror
    </label>

    <div class="form-grid">
        <label class="field">
            <span>Hasil atau pelajaran</span>
            <textarea name="learning" rows="4">{{ old('learning', $journal->learning ?? '') }}</textarea>
            @error('learning')
                <small>{{ $message }}</small>
            @enderror
        </label>

        <label class="field">
            <span>Kendala</span>
            <textarea name="obstacle" rows="4">{{ old('obstacle', $journal->obstacle ?? '') }}</textarea>
            @error('obstacle')
                <small>{{ $message }}</small>
            @enderror
        </label>
    </div>

    <label class="field">
        <span>Rencana berikutnya</span>
        <textarea name="next_plan" rows="3">{{ old('next_plan', $journal->next_plan ?? '') }}</textarea>
        @error('next_plan')
            <small>{{ $message }}</small>
        @enderror
    </label>

    @if ($isEdit && $journal->photo_path)
        @php($photoUrl = asset('storage/'.$journal->photo_path))
        <div class="current-photo">
            <img src="{{ $photoUrl }}" alt="Dokumentasi {{ $journal->title }}">
            <label class="check-field">
                <input type="checkbox" name="remove_photo" value="1">
                <span>Hapus foto lama</span>
            </label>
        </div>
    @endif

    <label class="file-field">
        <span>{{ $isEdit ? 'Ganti foto dokumentasi' : 'Foto dokumentasi' }}</span>
        <input class="photo-input" type="file" name="photo" accept="image/*">
        <strong>Pilih foto</strong>
        <em class="photo-name">{{ $isEdit && $journal->photo_original_name ? $journal->photo_original_name : 'Belum ada foto dipilih' }}</em>
        @error('photo')
            <small>{{ $message }}</small>
        @enderror
    </label>

    <div class="form-actions">
        <button class="primary-button" type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Simpan catatan' }}</button>
        @if ($isEdit)
            <a class="secondary-button" href="{{ route('journals.index') }}">Batal</a>
        @endif
    </div>
</form>
