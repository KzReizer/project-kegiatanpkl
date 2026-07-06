<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PklJournal extends Model
{
    protected $fillable = [
        'user_id',
        'activity_date',
        'title',
        'location',
        'category',
        'description',
        'learning',
        'obstacle',
        'next_plan',
        'photo_path',
        'photo_original_name',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PklJournalPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPrimaryPhotoPathAttribute(): ?string
    {
        return $this->photos->first()?->path ?? $this->photo_path;
    }
}
