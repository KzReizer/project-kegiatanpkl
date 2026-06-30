<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PklJournal extends Model
{
    protected $fillable = [
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
        ];
    }
}
