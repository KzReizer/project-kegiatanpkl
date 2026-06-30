@extends('layouts.app', ['title' => 'Edit Jurnal PKL'])

@section('content')
    <section class="edit-page">
        <header class="page-header compact-header">
            <div>
                <p class="eyebrow">Edit catatan</p>
                <h1>{{ $journal->title }}</h1>
            </div>
        </header>

        @include('journals._form', ['journal' => $journal])
    </section>
@endsection
