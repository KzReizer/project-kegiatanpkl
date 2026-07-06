<?php

namespace App\Http\Controllers;

use App\Models\PklJournal;
use App\Models\PklJournalPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function index(): View
    {
        $this->ensureAdmin();

        $users = User::query()
            ->withCount([
                'journals',
                'journals as active_journals_count' => fn (Builder $query) => $query->whereNull('archived_at'),
                'journals as archived_journals_count' => fn (Builder $query) => $query->whereNotNull('archived_at'),
            ])
            ->withMax('journals', 'activity_date')
            ->orderByDesc('journals_max_activity_date')
            ->orderBy('name')
            ->get();

        return view('admin.index', [
            'users' => $users,
            'totalUsers' => User::query()->count(),
            'totalJournals' => PklJournal::query()->count(),
            'totalPhotos' => PklJournalPhoto::query()->count(),
        ]);
    }

    public function show(Request $request, User $user): View
    {
        $this->ensureAdmin();

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'month' => $request->query('month'),
            'photo' => $request->query('photo'),
            'category' => $request->query('category'),
            'sort' => $request->query('sort', 'newest'),
            'status' => $request->query('status', 'active'),
        ];

        $journals = $user->journals()
            ->with('photos')
            ->withCount('photos')
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
            ->when($filters['category'], fn (Builder $query, string $category) => $query->where('category', $category))
            ->when($filters['photo'] === 'with', fn (Builder $query) => $query->whereHas('photos'))
            ->when($filters['photo'] === 'without', fn (Builder $query) => $query->doesntHave('photos'))
            ->tap(function (Builder $query) use ($filters): void {
                match ($filters['sort']) {
                    'oldest' => $query->oldest('activity_date')->oldest('id'),
                    'title' => $query->orderBy('title')->latest('activity_date'),
                    default => $query->latest('activity_date')->latest('id'),
                };
            })
            ->paginate(10)
            ->withQueryString();

        return view('admin.show', [
            'user' => $user,
            'journals' => $journals,
            'groupedJournals' => $journals->getCollection()
                ->groupBy(fn (PklJournal $journal) => $journal->activity_date->locale('id')->translatedFormat('F Y'))
                ->map(fn ($monthGroup) => $monthGroup->groupBy(fn (PklJournal $journal) => $journal->activity_date->locale('id')->translatedFormat('d F Y')))
                ->all(),
            'filters' => $filters,
            'categories' => ['Kegiatan', 'Dokumentasi', 'Bimbingan', 'Kendala', 'Selesai'],
            'filteredCount' => $journals->total(),
        ]);
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
