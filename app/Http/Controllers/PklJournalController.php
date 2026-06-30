<?php

namespace App\Http\Controllers;

use App\Models\PklJournal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PklJournalController extends Controller
{
    private const CATEGORIES = [
        'Kegiatan',
        'Dokumentasi',
        'Bimbingan',
        'Kendala',
        'Selesai',
    ];

    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'from', 'to', 'photo', 'category']);
        $journals = $this->journalQuery($filters)
            ->latest('activity_date')
            ->latest('id')
            ->paginate(8)
            ->withQueryString();

        return view('journals.index', [
            'journals' => $journals,
            'stats' => $this->stats(),
            'filters' => $filters,
            'categories' => self::CATEGORIES,
            'latestJournal' => PklJournal::query()->latest('activity_date')->latest('id')->first(),
            'filteredCount' => $journals->total(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        unset($validated['photo'], $validated['remove_photo']);

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $validated['photo_path'] = $photo->store('pkl-photos', 'public');
            $validated['photo_original_name'] = $photo->getClientOriginalName();
        }

        PklJournal::create($validated);

        return redirect()
            ->route('journals.index')
            ->with('status', 'Catatan PKL berhasil disimpan.');
    }

    public function edit(PklJournal $journal): View
    {
        return view('journals.edit', [
            'journal' => $journal,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function update(Request $request, PklJournal $journal): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $removePhoto = $request->boolean('remove_photo');
        unset($validated['photo'], $validated['remove_photo']);

        if ($removePhoto && $journal->photo_path) {
            Storage::disk('public')->delete($journal->photo_path);
            $validated['photo_path'] = null;
            $validated['photo_original_name'] = null;
        }

        if ($request->hasFile('photo')) {
            if ($journal->photo_path) {
                Storage::disk('public')->delete($journal->photo_path);
            }

            $photo = $request->file('photo');
            $validated['photo_path'] = $photo->store('pkl-photos', 'public');
            $validated['photo_original_name'] = $photo->getClientOriginalName();
        }

        $journal->update($validated);

        return redirect()
            ->route('journals.index')
            ->with('status', 'Catatan PKL berhasil diperbarui.');
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

    public function print(Request $request): View
    {
        $filters = $request->only(['q', 'from', 'to', 'photo', 'category']);
        $journals = $this->journalQuery($filters)
            ->oldest('activity_date')
            ->oldest('id')
            ->get();

        return view('journals.print', [
            'journals' => $journals,
            'filters' => $filters,
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'title' => ['required', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'category' => ['required', 'string', Rule::in(self::CATEGORIES)],
            'description' => ['required', 'string', 'max:5000'],
            'learning' => ['nullable', 'string', 'max:5000'],
            'obstacle' => ['nullable', 'string', 'max:5000'],
            'next_plan' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'remove_photo' => ['nullable', 'boolean'],
        ], [
            'activity_date.required' => 'Tanggal kegiatan wajib diisi.',
            'activity_date.before_or_equal' => 'Tanggal kegiatan tidak boleh melebihi hari ini.',
            'title.required' => 'Judul kegiatan wajib diisi.',
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'description.required' => 'Catatan kegiatan wajib diisi.',
            'photo.image' => 'File dokumentasi harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 5 MB.',
        ]);
    }

    private function journalQuery(array $filters): Builder
    {
        return PklJournal::query()
            ->when($filters['q'] ?? null, function (Builder $query, string $keyword): void {
                $query->where(function (Builder $query) use ($keyword): void {
                    $query
                        ->where('title', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%")
                        ->orWhere('learning', 'like', "%{$keyword}%")
                        ->orWhere('obstacle', 'like', "%{$keyword}%")
                        ->orWhere('next_plan', 'like', "%{$keyword}%")
                        ->orWhere('location', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('activity_date', '>=', $date))
            ->when($filters['to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('activity_date', '<=', $date))
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => $query->where('category', $category))
            ->when(($filters['photo'] ?? null) === 'with', fn (Builder $query) => $query->whereNotNull('photo_path'))
            ->when(($filters['photo'] ?? null) === 'without', fn (Builder $query) => $query->whereNull('photo_path'));
    }

    private function stats(): array
    {
        return [
            'days' => PklJournal::query()->distinct()->count('activity_date'),
            'entries' => PklJournal::query()->count(),
            'photos' => PklJournal::query()->whereNotNull('photo_path')->count(),
            'this_month' => PklJournal::query()
                ->whereBetween('activity_date', [
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString(),
                ])
                ->count(),
        ];
    }
}
