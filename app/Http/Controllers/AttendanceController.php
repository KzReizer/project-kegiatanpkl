<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // Koordinat lokasi kantor (lat, lng) dan radius validasi GPS dalam km
    // BISA DIUBAH SESUAI LOKASI KANTOR ANDA
    private const OFFICE_LATITUDE = -0.4676098266882537;      // Ganti dengan latitude kantor
    private const OFFICE_LONGITUDE = 117.15770323636485;    // Ganti dengan longitude kantor
    private const RADIUS_KM = 1; // Radius dalam km

    /**
     * Tampilkan daftar absensi user
     */
    public function index()
    {
        $user = Auth::user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $attendances = $user->attendances()
            ->whereYear('attendance_date', $currentYear)
            ->whereMonth('attendance_date', $currentMonth)
            ->orderBy('attendance_date', 'desc')
            ->paginate(15);

        // Statistik
        $stats = [
            'hadir' => $user->attendances()
                ->whereYear('attendance_date', $currentYear)
                ->whereMonth('attendance_date', $currentMonth)
                ->where('status', 'hadir')
                ->count(),
            'terlambat' => $user->attendances()
                ->whereYear('attendance_date', $currentYear)
                ->whereMonth('attendance_date', $currentMonth)
                ->where('status', 'terlambat')
                ->count(),
            'sakit' => $user->attendances()
                ->whereYear('attendance_date', $currentYear)
                ->whereMonth('attendance_date', $currentMonth)
                ->where('status', 'sakit')
                ->count(),
            'izin' => $user->attendances()
                ->whereYear('attendance_date', $currentYear)
                ->whereMonth('attendance_date', $currentMonth)
                ->where('status', 'izin')
                ->count(),
            'alpha' => $user->attendances()
                ->whereYear('attendance_date', $currentYear)
                ->whereMonth('attendance_date', $currentMonth)
                ->where('status', 'alpha')
                ->count(),
        ];

        $totalWorkDays = $stats['hadir'] + $stats['terlambat'] + $stats['sakit'] + $stats['izin'];
        $percentage = $totalWorkDays > 0 ? round(($stats['hadir'] + $stats['terlambat']) / $totalWorkDays * 100, 2) : 0;

        return view('attendances.index', [
            'attendances' => $attendances,
            'stats' => $stats,
            'percentage' => $percentage,
        ]);
    }

    /**
     * Check-in user
     */
    public function checkIn(Request $request)
    {
        $user = Auth::user();
        
        // Validasi request
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'device' => 'nullable|string',
        ]);

        // Cek apakah sudah check-in hari ini
        $today = Attendance::todayCheckin($user->id);
        if ($today) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah check-in hari ini!',
            ], 422);
        }

        // Validasi GPS - apakah dalam radius kantor
        $distance = $this->calculateDistance(
            $validated['latitude'],
            $validated['longitude'],
            self::OFFICE_LATITUDE,
            self::OFFICE_LONGITUDE
        );

        if ($distance > self::RADIUS_KM) {
            return response()->json([
                'success' => false,
                'message' => "Anda berada di luar radius lokasi kantor ({$distance} km dari kantor). Minimal harus dalam 1 km.",
                'distance' => $distance,
            ], 422);
        }

        // Tentukan status berdasarkan jam
        $currentTime = now();
        $checkInDeadline = $currentTime->copy()->setTime(10, 0, 0);
        $status = $currentTime->lessThanOrEqualTo($checkInDeadline) ? 'hadir' : 'terlambat';

        // Buat record absensi
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => today(),
            'check_in_time' => $currentTime,
            'check_in_latitude' => $validated['latitude'],
            'check_in_longitude' => $validated['longitude'],
            'check_in_device' => $validated['device'] ?? null,
            'status' => $status,
        ]);

        return response()->json([
            'success' => true,
            'message' => $status === 'hadir' ? '✅ Check-in berhasil!' : '⚠️ Check-in berhasil, tapi Anda terlambat!',
            'attendance' => $attendance,
            'status' => $status,
        ]);
    }

    /**
     * Check-out user
     */
    public function checkOut(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'device' => 'nullable|string',
        ]);

        // Cari attendance hari ini
        $attendance = Attendance::todayCheckin($user->id);
        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum check-in hari ini!',
            ], 422);
        }

        if ($attendance->hasCheckedOut()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah check-out hari ini!',
            ], 422);
        }

        // Validasi GPS
        $distance = $this->calculateDistance(
            $validated['latitude'],
            $validated['longitude'],
            self::OFFICE_LATITUDE,
            self::OFFICE_LONGITUDE
        );

        if ($distance > self::RADIUS_KM) {
            return response()->json([
                'success' => false,
                'message' => "Anda berada di luar radius lokasi kantor ({$distance} km dari kantor). Minimal harus dalam 1 km.",
                'distance' => $distance,
            ], 422);
        }

        // Validasi jam check-out (minimal jam 16:00)
        $currentTime = now();
        $checkOutDeadline = $currentTime->copy()->setTime(16, 0, 0);
        if ($currentTime->lessThan($checkOutDeadline)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum bisa check-out. Check-out bisa dilakukan setelah jam 16:00.',
            ], 422);
        }

        // Update attendance
        $attendance->update([
            'check_out_time' => $currentTime,
            'check_out_latitude' => $validated['latitude'],
            'check_out_longitude' => $validated['longitude'],
            'check_out_device' => $validated['device'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => '📤 Check-out berhasil!',
            'attendance' => $attendance,
            'duration' => $attendance->duration,
        ]);
    }

    /**
     * Ambil status absensi untuk dashboard
     */
    public function getTodayStatus()
    {
        $user = Auth::user();
        $attendance = Attendance::todayCheckin($user->id);

        if (!$attendance) {
            return response()->json([
                'status' => 'not_checked_in',
                'message' => 'Anda belum check-in',
            ]);
        }

        return response()->json([
            'status' => 'checked_in',
            'attendance' => $attendance,
            'checked_out' => $attendance->hasCheckedOut(),
            'check_in_time' => $attendance->check_in_time_formatted,
            'check_out_time' => $attendance->check_out_time_formatted,
        ]);
    }

    /**
     * Laporan absensi untuk admin
     */
    public function report(Request $request)
    {
        // Hanya admin yang bisa akses
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $userId = $request->query('user_id', null);

        $query = Attendance::query()
            ->with('user')
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->orderBy('attendance_date', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $attendances = $query->paginate(50);
        $users = User::where('role', '!=', 'admin')->get();

        return view('admin.attendances.report', [
            'attendances' => $attendances,
            'users' => $users,
            'month' => $month,
            'year' => $year,
            'selectedUserId' => $userId,
        ]);
    }

    /**
     * Admin set status manual (untuk izin/sakit)
     */
    public function setStatus(Request $request, Attendance $attendance)
    {
        // Hanya admin yang bisa akses
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'status' => 'required|in:hadir,terlambat,sakit,izin,alpha',
            'notes' => 'nullable|string|max:500',
        ]);

        $attendance->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Status absensi berhasil diubah!',
            'attendance' => $attendance,
        ]);
    }

    /**
     * Hitung jarak antara dua titik koordinat menggunakan Haversine formula
     * Return jarak dalam km
     */
    private function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371; // Radius bumi dalam km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }
}
