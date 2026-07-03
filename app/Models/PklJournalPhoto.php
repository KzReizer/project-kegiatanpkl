<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PklJournalPhoto extends Model
{
    protected $fillable = [
        'path',
        'original_name',
        'sort_order',
    ];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(PklJournal::class, 'pkl_journal_id');
    }
}
