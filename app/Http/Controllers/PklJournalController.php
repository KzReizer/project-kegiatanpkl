<?php

namespace App\Http\Controllers;

use App\Models\PklJournal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PklJournalController extends Controller
{
    public function index(): View
    {
        $journals = PklJournal::query()
            ->latest('activity_date')
            ->latest('id')
            ->get();

        return view('welcome', [
            'journals' => $journals,
            'totalDays' => $journals->pluck('activity_date')->unique()->count(),
            'totalPhotos' => $journals->whereNotNull('photo_path')->count(),
            'latestJournal' => $journals->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ], [
            'activity_date.required' => 'Tanggal kegiatan wajib diisi.',
            'activity_date.before_or_equal' => 'Tanggal kegiatan tidak boleh melebihi hari ini.',
            'title.required' => 'Judul kegiatan wajib diisi.',
            'description.required' => 'Catatan kegiatan wajib diisi.',
            'photo.image' => 'File dokumentasi harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');

            $validated['photo_path'] = $photo->store('pkl-photos', 'public');
            $validated['photo_original_name'] = $photo->getClientOriginalName();
        }

        unset($validated['photo']);

        PklJournal::create($validated);

        return redirect()
            ->route('journals.index')
            ->with('status', 'Catatan PKL berhasil disimpan.');
    }

    public function destroy(PklJournal $journal): RedirectResponse
    {
        if ($journal->photo_path) {
            Storage::disk('public')->delete($journal->photo_path);
        }

        $journal->delete();

        return redirect()
            ->route('journals.index')
            ->with('status', 'Catatan PKL berhasil dihapus.');
    }
}
