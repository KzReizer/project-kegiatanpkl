<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'attendance_date',
        'check_in_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_in_device',
        'check_out_time',
        'check_out_latitude',
        'check_out_longitude',
        'check_out_device',
        'status',
        'notes',
        'sick_letter',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_time' => 'datetime',
            'check_out_time' => 'datetime',
        ];
    }

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cek apakah user sudah check-in hari ini
     */
    public static function todayCheckin(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->where('attendance_date', today())
            ->first();
    }

    /**
     * Cek apakah user sudah check-out hari ini
     */
    public function hasCheckedOut(): bool
    {
        return $this->check_out_time !== null;
    }

    /**
     * Hitung durasi kerja dalam jam
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $checkOut = Carbon::parse($this->check_out_time);
        $duration = $checkOut->diffInMinutes($checkIn);

        $hours = intdiv($duration, 60);
        $minutes = $duration % 60;

        return "{$hours}h {$minutes}m";
    }

    /**
     * Status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'hadir' => 'success',
            'terlambat' => 'warning',
            'sakit' => 'info',
            'izin' => 'secondary',
            'alpha' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'hadir' => '✅ Hadir',
            'terlambat' => '⚠️ Terlambat',
            'sakit' => '🏥 Sakit',
            'izin' => '📋 Izin',
            'alpha' => '❌ Alpha',
            default => 'Unknown',
        };
    }

    /**
     * Get check-in time formatted
     */
    public function getCheckInTimeFormattedAttribute(): ?string
    {
        return $this->check_in_time 
            ? Carbon::parse($this->check_in_time)->format('H:i')
            : '-';
    }

    /**
     * Get check-out time formatted
     */
    public function getCheckOutTimeFormattedAttribute(): ?string
    {
        return $this->check_out_time 
            ? Carbon::parse($this->check_out_time)->format('H:i')
            : '-';
    }
}
