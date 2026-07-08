@extends('layouts.app', ['title' => 'Edit Jurnal PKL'])

@section('content')
    <section class="edit-page">
        <header class="page-header compact-header">
            <div>
                <p class="eyebrow">Edit catatan</p>
                <h1>{{ $journal->title }}</h1>
                <p class="subtle-text">Perbarui detail kegiatan, dokumentasi, atau rencana tindak lanjut tanpa mengubah riwayat lainnya.</p>
            </div>
        </header>

        @include('journals._form', ['journal' => $journal])
    </section>
@endsection
