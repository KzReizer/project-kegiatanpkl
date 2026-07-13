@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">📍 Absensi</h1>
    </div>

    <!-- Check-In Card -->
    @include('attendances._card-checkin')

    <div style="margin-top: 2rem;"></div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="text-sm text-gray-600">Hadir</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['hadir'] }}</div>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="text-sm text-gray-600">Terlambat</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['terlambat'] }}</div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="text-sm text-gray-600">Sakit</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats['sakit'] }}</div>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <div class="text-sm text-gray-600">Izin</div>
            <div class="text-2xl font-bold text-gray-600">{{ $stats['izin'] }}</div>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="text-sm text-gray-600">Alpha</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['alpha'] }}</div>
        </div>
    </div>

    <!-- Persentase Kehadiran -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Persentase Kehadiran Bulan Ini</h3>
        <div class="w-full bg-gray-200 rounded-full h-4">
            <div class="bg-green-500 h-4 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
        </div>
        <div class="text-right mt-2 text-sm text-gray-600">{{ $percentage }}%</div>
    </div>

    <!-- Tabel Absensi -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Riwayat Absensi Bulan Ini</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tanggal</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Check-In</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Check-Out</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Durasi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($attendances as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $record->attendance_date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $record->check_in_time_formatted }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $record->check_out_time_formatted }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $record->duration ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $record->status_color }}-100 text-{{ $record->status_color }}-800">
                                    {{ $record->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                {{ $record->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-600">
                                Belum ada data absensi untuk bulan ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($attendances->count() > 0)
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    // JavaScript untuk real-time status di halaman index bisa ditambahkan di sini jika perlu
</script>
@endsection
