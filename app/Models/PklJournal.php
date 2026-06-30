<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PklJournal extends Model
{
    /** @use HasFactory<\Database\Factories\PklJournalFactory> */
    use HasFactory;

    protected $fillable = [
        'activity_date',
        'title',
        'description',
        'photo_path',
        'photo_original_name',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
        ];
    }
}
