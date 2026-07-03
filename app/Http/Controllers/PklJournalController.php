<?php

namespace App\Http\Controllers;

use App\Models\PklJournal;
use App\Models\PklJournalPhoto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $filters = $this->filters($request);
        $journals = $this->journalQuery($filters)
            ->with('photos')
            ->withCount('photos')
            ->tap(fn (Builder $query) => $this->applySort($query, $filters['sort']))
            ->paginate(10)
            ->withQueryString();

        return view('journals.index', [
            'journals' => $journals,
            'groupedJournals' => $this->groupJournals($journals->getCollection()),
            'stats' => $this->stats(),
            'filters' => $filters,
            'categories' => self::CATEGORIES,
            'latestJournal' => PklJournal::query()->whereNull('archived_at')->latest('activity_date')->latest('id')->first(),
            'filteredCount' => $journals->total(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        unset($validated['photo'], $validated['photos'], $validated['remove_photo_ids']);

        $journal = PklJournal::create($validated);
        $this->storePhotos($journal, $request->file('photos', []));
        $this->storePhotos($journal, array_filter([$request->file('photo')]));
        $this->syncPrimaryPhoto($journal);

        return redirect()
            ->route('journals.index')
            ->with('status', 'Catatan PKL berhasil disimpan.');
    }

    public function edit(PklJournal $journal): View
    {
        $journal->load('photos');

        return view('journals.edit', [
            'journal' => $journal,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function update(Request $request, PklJournal $journal): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $removePhotoIds = collect($validated['remove_photo_ids'] ?? [])->map(fn ($id) => (int) $id)->all();
        unset($validated['photo'], $validated['photos'], $validated['remove_photo_ids']);

        $journal->update($validated);
        $this->deletePhotos($journal, $removePhotoIds);
        $this->storePhotos($journal, $request->file('photos', []));
        $this->storePhotos($journal, array_filter([$request->file('photo')]));
        $this->syncPrimaryPhoto($journal);

        return redirect()
            ->route('journals.index')
            ->with('status', 'Catatan PKL berhasil diperbarui.');
    }

    public function duplicate(PklJournal $journal): RedirectResponse
    {
        $journal->load('photos');

        $copy = $journal->replicate([
            'photo_path',
            'photo_original_name',
            'archived_at',
            'created_at',
            'updated_at',
        ]);
        $copy->title = Str::limit('Salinan - '.$journal->title, 120, '');
        $copy->activity_date = now()->toDateString();
        $copy->archived_at = null;
        $copy->photo_path = null;
        $copy->photo_original_name = null;
        $copy->save();

        foreach ($journal->photos as $photo) {
            $extension = pathinfo($photo->path, PATHINFO_EXTENSION);
            $target = 'pkl-photos/'.Str::uuid().($extension ? ".{$extension}" : '');

            if (Storage::disk('public')->exists($photo->path) && Storage::disk('public')->copy($photo->path, $target)) {
                $copy->photos()->create([
                    'path' => $target,
                    'original_name' => $photo->original_name,
                    'sort_order' => $photo->sort_order,
                ]);
            }
        }

        $this->syncPrimaryPhoto($copy);

        return redirect()
            ->route('journals.edit', $copy)
            ->with('status', 'Catatan berhasil diduplikat. Tinggal sesuaikan tanggal atau detailnya.');
    }

    public function archive(PklJournal $journal): RedirectResponse
    {
        $journal->forceFill([
            'archived_at' => $journal->archived_at ? null : now(),
        ])->save();

        return redirect()
            ->route('journals.index', request()->query())
            ->with('status', $journal->archived_at ? 'Catatan masuk arsip.' : 'Catatan dikembalikan dari arsip.');
    }

    public function destroy(PklJournal $journal): RedirectResponse
    {
        $journal->load('photos');

        foreach ($journal->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

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
        $filters = $this->filters($request);
        $journals = $this->journalQuery($filters)
            ->with('photos')
            ->oldest('activity_date')
            ->oldest('id')
            ->get();

        return view('journals.print', [
            'journals' => $journals,
            'filters' => $filters,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $journals = $this->journalQuery($filters)
            ->withCount('photos')
            ->tap(fn (Builder $query) => $this->applySort($query, $filters['sort']))
            ->get();

        return response()->streamDownload(function () use ($journals): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Tanggal',
                'Judul',
                'Tempat',
                'Kategori',
                'Catatan',
                'Hasil',
                'Kendala',
                'Rencana',
                'Jumlah Foto',
                'Status',
            ]);

            foreach ($journals as $journal) {
                fputcsv($handle, [
                    $journal->activity_date->toDateString(),
                    $journal->title,
                    $journal->location,
                    $journal->category,
                    $journal->description,
                    $journal->learning,
                    $journal->obstacle,
                    $journal->next_plan,
                    $journal->photos_count,
                    $journal->archived_at ? 'Arsip' : 'Aktif',
                ]);
            }

            fclose($handle);
        }, 'jurnal-pkl-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv',
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
            'photos' => ['nullable', 'array', 'max:12'],
            'photos.*' => ['image', 'max:5120'],
            'remove_photo_ids' => ['nullable', 'array'],
            'remove_photo_ids.*' => ['integer'],
        ], [
            'activity_date.required' => 'Tanggal kegiatan wajib diisi.',
            'activity_date.before_or_equal' => 'Tanggal kegiatan tidak boleh melebihi hari ini.',
            'title.required' => 'Judul kegiatan wajib diisi.',
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'description.required' => 'Catatan kegiatan wajib diisi.',
            'photo.image' => 'File dokumentasi harus berupa gambar.',
            'photos.max' => 'Maksimal 12 foto per catatan.',
            'photos.*.image' => 'Semua file dokumentasi harus berupa gambar.',
            'photos.*.max' => 'Ukuran tiap foto maksimal 5 MB.',
        ]);
    }

    private function filters(Request $request): array
    {
        return [
            'q' => trim((string) $request->query('q', '')),
            'day' => $request->query('day'),
            'month' => $request->query('month'),
            'photo' => $request->query('photo'),
            'category' => $request->query('category'),
            'sort' => $request->query('sort', 'newest'),
            'status' => $request->query('status', 'active'),
        ];
    }

    private function journalQuery(array $filters): Builder
    {
        return PklJournal::query()
            ->when($filters['status'] === 'active', fn (Builder $query) => $query->whereNull('archived_at'))
            ->when($filters['status'] === 'archived', fn (Builder $query) => $query->whereNotNull('archived_at'))
            ->when($filters['q'], function (Builder $query, string $keyword): void {
                $query->where(function (Builder $query) use ($keyword): void {
                    $query
                        ->where('title', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%")
                        ->orWhere('learning', 'like', "%{$keyword}%")
                        ->orWhere('obstacle', 'like', "%{$keyword}%")
                        ->orWhere('next_plan', 'like', "%{$keyword}%")
                        ->orWhere('category', 'like', "%{$keyword}%")
                        ->orWhere('location', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['month'], function (Builder $query, string $month): void {
                $query->whereYear('activity_date', substr($month, 0, 4))
                    ->whereMonth('activity_date', substr($month, 5, 2));
            })
            ->when($filters['day'], fn (Builder $query, string $date) => $query->whereDate('activity_date', $date))
            ->when($filters['category'], fn (Builder $query, string $category) => $query->where('category', $category))
            ->when($filters['photo'] === 'with', fn (Builder $query) => $query->whereHas('photos'))
            ->when($filters['photo'] === 'without', fn (Builder $query) => $query->doesntHave('photos'));
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->oldest('activity_date')->oldest('id'),
            'title' => $query->orderBy('title')->latest('activity_date'),
            default => $query->latest('activity_date')->latest('id'),
        };
    }

    private function stats(): array
    {
        return [
            'today' => PklJournal::query()->whereDate('activity_date', now()->toDateString())->whereNull('archived_at')->count(),
            'days' => PklJournal::query()->whereNull('archived_at')->distinct()->count('activity_date'),
            'entries' => PklJournal::query()->whereNull('archived_at')->count(),
            'photos' => PklJournalPhoto::query()->whereHas('journal', fn (Builder $query) => $query->whereNull('archived_at'))->count(),
            'this_month' => PklJournal::query()
                ->whereNull('archived_at')
                ->whereBetween('activity_date', [
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString(),
                ])
                ->count(),
            'archived' => PklJournal::query()->whereNotNull('archived_at')->count(),
        ];
    }

    private function groupJournals($journals): array
    {
        return $journals
            ->groupBy(fn (PklJournal $journal) => $journal->activity_date->locale('id')->translatedFormat('F Y'))
            ->map(fn ($monthGroup) => $monthGroup->groupBy(fn (PklJournal $journal) => $journal->activity_date->locale('id')->translatedFormat('d F Y')))
            ->all();
    }

    /**
     * @param  array<int, UploadedFile>|UploadedFile[]  $photos
     */
    private function storePhotos(PklJournal $journal, array $photos): void
    {
        $startOrder = $journal->photos()->count();

        foreach (array_values($photos) as $index => $photo) {
            if (! $photo instanceof UploadedFile) {
                continue;
            }

            $journal->photos()->create([
                'path' => $photo->store('pkl-photos', 'public'),
                'original_name' => $photo->getClientOriginalName(),
                'sort_order' => $startOrder + $index,
            ]);
        }
    }

    /**
     * @param  array<int, int>  $photoIds
     */
    private function deletePhotos(PklJournal $journal, array $photoIds): void
    {
        if ($photoIds === []) {
            return;
        }

        $photos = $journal->photos()->whereIn('id', $photoIds)->get();

        foreach ($photos as $photo) {
            Storage::disk('public')->delete($photo->path);
            $photo->delete();
        }
    }

    private function syncPrimaryPhoto(PklJournal $journal): void
    {
        $primary = $journal->photos()->orderBy('sort_order')->orderBy('id')->first();

        $journal->forceFill([
            'photo_path' => $primary?->path,
            'photo_original_name' => $primary?->original_name,
        ])->save();
    }
}
