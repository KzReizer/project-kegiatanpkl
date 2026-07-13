@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">📊 Laporan Absensi</h1>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih User</label>
                <select name="user_id" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <select name="month" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ now()->setMonth($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                <select name="year" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                    @for($y = now()->year - 1; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    🔍 Filter
                </button>
                <button type="button" onclick="printReport()" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    🖨️ Cetak
                </button>
                <button type="button" onclick="exportToExcel()" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-lg transition">
                    📥 Excel
                </button>
            </div>
        </form>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                Laporan Absensi 
                @if($selectedUserId)
                    - {{ $users->find($selectedUserId)?->name }}
                @else
                    - Semua User
                @endif
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nama</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Tanggal</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Check-In</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Check-Out</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Durasi</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Catatan</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($attendances as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $record->user->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
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
                            <td class="px-6 py-4 text-sm">
                                <button type="button" onclick="openEditModal({{ $record->id }}, '{{ $record->status }}')" class="text-blue-600 hover:text-blue-900 font-medium">
                                    ✏️ Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-600">
                                Belum ada data absensi untuk periode ini
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

<!-- Edit Status Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Edit Status Absensi</h3>
        </div>
        <form id="editForm" class="p-6 space-y-4">
            <input type="hidden" id="attendanceId">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="statusSelect" name="status" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="hadir">✅ Hadir</option>
                    <option value="terlambat">⚠️ Terlambat</option>
                    <option value="sakit">🏥 Sakit</option>
                    <option value="izin">📋 Izin</option>
                    <option value="alpha">❌ Alpha</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea id="notesInput" name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Masukkan catatan (opsional)"></textarea>
            </div>

            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, currentStatus) {
        document.getElementById('attendanceId').value = id;
        document.getElementById('statusSelect').value = currentStatus;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    document.getElementById('editForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('attendanceId').value;
        const status = document.getElementById('statusSelect').value;
        const notes = document.getElementById('notesInput').value;

        try {
            const response = await fetch(`/absensi/${id}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-HTTP-Method-Override': 'PATCH',
                },
                body: JSON.stringify({ status, notes })
            });

            const data = await response.json();
            if (data.success) {
                alert('✅ Status absensi berhasil diubah!');
                location.reload();
            }
        } catch (error) {
            alert('Terjadi kesalahan: ' + error.message);
        }
    });

    function printReport() {
        window.print();
    }

    function exportToExcel() {
        const table = document.querySelector('table');
        const html = table.outerHTML;
        const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `laporan-absensi-${new Date().toISOString().split('T')[0]}.xls`;
        a.click();
    }
</script>

<style>
    @media print {
        body {
            background-color: white;
        }
        .container {
            max-width: 100%;
        }
        button, form, #editModal {
            display: none;
        }
    }
</style>
@endsection
