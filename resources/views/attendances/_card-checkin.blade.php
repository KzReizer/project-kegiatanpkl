<!-- Check-In/Check-Out Card for Dashboard -->
<div class="profile-card">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Absensi</p>
            <h2>📍 Check-In / Check-Out</h2>
        </div>
        <span class="card-icon" id="attendance-icon">
            <i data-lucide="clock"></i>
        </span>
    </div>

    <div id="attendance-status" class="attendance-content">
        <!-- Loading state -->
        <div class="text-center py-4" style="padding: 2rem;">
            <p style="color: #888;">Memuat status absensi...</p>
        </div>
    </div>

    <!-- Testing Mode Toggle (untuk development) -->
    @if(env('APP_DEBUG') === true || auth()->user()->isAdmin())
    <div style="border-top: 1px solid #eee; padding: 1rem; margin-top: 1rem;">
        <details style="cursor: pointer;">
            <summary style="color: #888; font-size: 0.9rem;">🧪 Testing Mode (GPS Manual)</summary>
            <div id="testingForm" style="display: none; margin-top: 1rem; padding: 1rem; background: #f5f5f5; border-radius: 8px;">
                <p style="font-size: 0.85rem; color: #666; margin-bottom: 1rem;">
                    📍 <strong>Koordinat Kantor Sekarang:</strong><br>
                    Lat: -0.4676098266882537 | Lng: 117.15770323636485 | Radius: 1 km<br><br>
                    <a href="https://www.google.com/maps" target="_blank" style="color: #0066cc;">👉 Buka Google Maps untuk cari koordinat</a>
                </p>

                <div style="display: grid; gap: 0.75rem;">
                    <div>
                        <label style="font-size: 0.85rem; color: #666; display: block; margin-bottom: 0.25rem;">Latitude:</label>
                        <input type="number" id="testLat" step="0.0001" placeholder="-0.4676098266882537" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-size: 0.85rem; color: #666; display: block; margin-bottom: 0.25rem;">Longitude:</label>
                        <input type="number" id="testLng" step="0.0001" placeholder="117.15770323636485" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <button type="button" onclick="testCheckInManual()" class="primary-button" style="margin-top: 0.5rem;">
                        🧪 Test Check-In
                    </button>
                    <button type="button" onclick="testCheckOutManual()" class="primary-button" style="margin-top: 0.25rem; background-color: #666;">
                        🧪 Test Check-Out
                    </button>
                </div>
            </div>
        </details>
    </div>
    @endif
</div>

<script>
    // Toggle testing form
    document.querySelectorAll('details').forEach(detail => {
        detail.addEventListener('toggle', () => {
            if (detail.open) {
                const testingForm = detail.querySelector('#testingForm');
                if (testingForm) testingForm.style.display = 'block';
            }
        });
    });

    // Update jam secara real-time
    function updateTime() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    updateTime();
    setInterval(updateTime, 1000);

    // Muat status absensi
    async function loadAttendanceStatus() {
        try {
            const response = await fetch('/absensi/status', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });
            const data = await response.json();

            const statusDiv = document.getElementById('attendance-status');
            const icon = document.getElementById('attendance-icon');

            if (data.status === 'not_checked_in') {
                icon.innerHTML = '<i data-lucide="circle-x"></i>';
                statusDiv.innerHTML = `
                    <div style="padding: 2rem; text-align: center;">
                        <p style="color: #888; margin-bottom: 1rem;">Belum check-in hari ini</p>
                        <button onclick="handleCheckIn()" class="primary-button" style="width: 100%;">
                            ✓ CHECK-IN SEKARANG
                        </button>
                        <p style="color: #aaa; font-size: 0.9rem; margin-top: 1rem;">
                            📌 Batas tepat waktu: 08:30<br>
                            📍 GPS akan divalidasi (radius 1 km)
                        </p>
                    </div>
                `;
            } else if (data.status === 'checked_in') {
                icon.innerHTML = '<i data-lucide="check-circle"></i>';
                const checkedOut = data.checked_out;
                const html = `
                    <div style="padding: 2rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div style="background: #f0f9ff; padding: 1rem; border-radius: 8px; border: 1px solid #bfdbfe;">
                                <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Check-In</p>
                                <p style="font-size: 1.5rem; font-weight: bold; color: #16a34a;">${data.check_in_time}</p>
                            </div>
                            <div style="background: #f0fdf4; padding: 1rem; border-radius: 8px; border: 1px solid #bbf7d0;">
                                <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Check-Out</p>
                                <p style="font-size: 1.5rem; font-weight: bold; color: #16a34a;">${data.check_out_time}</p>
                            </div>
                        </div>
                        ${!checkedOut ? `
                            <button onclick="handleCheckOut()" class="primary-button" style="width: 100%;">
                                📤 CHECK-OUT
                            </button>
                            <p style="color: #aaa; font-size: 0.9rem; margin-top: 1rem;">
                                ⏰ Check-out bisa dilakukan setelah jam 16:00
                            </p>
                        ` : `
                            <div style="text-align: center; padding: 1rem; background: #f0fdf4; border-radius: 8px; color: #16a34a; font-weight: 500;">
                                ✅ Anda sudah check-out
                            </div>
                        `}
                    </div>
                `;
                statusDiv.innerHTML = html;
            }
        } catch (error) {
            console.error('Error loading attendance status:', error);
            document.getElementById('attendance-status').innerHTML = `
                <div style="padding: 2rem; color: #dc2626; text-align: center;">
                    ❌ Terjadi kesalahan saat memuat status absensi
                </div>
            `;
        }
    }

    // Handle Check-In
    async function handleCheckIn() {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung GPS');
            return;
        }

        // Minta izin GPS
        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const { latitude, longitude } = position.coords;
                await submitCheckIn(latitude, longitude);
            },
            (error) => {
                alert('⚠️ Izin GPS ditolak. Harap izinkan akses lokasi untuk check-in.');
            }
        );
    }

    // Handle Check-Out
    async function handleCheckOut() {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung GPS');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const { latitude, longitude } = position.coords;
                await submitCheckOut(latitude, longitude);
            },
            (error) => {
                alert('⚠️ Izin GPS ditolak. Harap izinkan akses lokasi untuk check-out.');
            }
        );
    }

    // Test Check-In Manual (untuk testing)
    async function testCheckInManual() {
        const lat = document.getElementById('testLat').value;
        const lng = document.getElementById('testLng').value;

        if (!lat || !lng) {
            alert('Isi latitude dan longitude terlebih dahulu');
            return;
        }

        await submitCheckIn(parseFloat(lat), parseFloat(lng));
    }

    // Test Check-Out Manual (untuk testing)
    async function testCheckOutManual() {
        const lat = document.getElementById('testLat').value;
        const lng = document.getElementById('testLng').value;

        if (!lat || !lng) {
            alert('Isi latitude dan longitude terlebih dahulu');
            return;
        }

        await submitCheckOut(parseFloat(lat), parseFloat(lng));
    }

    // Submit Check-In
    async function submitCheckIn(latitude, longitude) {
        try {
            const response = await fetch('/absensi/check-in', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    latitude,
                    longitude,
                    device: navigator.userAgent.substring(0, 100),
                })
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                loadAttendanceStatus();
            } else {
                alert('❌ ' + data.message);
            }
        } catch (error) {
            alert('Terjadi kesalahan: ' + error.message);
        }
    }

    // Submit Check-Out
    async function submitCheckOut(latitude, longitude) {
        try {
            const response = await fetch('/absensi/check-out', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    latitude,
                    longitude,
                    device: navigator.userAgent.substring(0, 100),
                })
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message);
                loadAttendanceStatus();
            } else {
                alert('❌ ' + data.message);
            }
        } catch (error) {
            alert('Terjadi kesalahan: ' + error.message);
        }
    }

    // Muat status saat halaman dimuat
    loadAttendanceStatus();

    // Refresh status setiap 30 detik
    setInterval(loadAttendanceStatus, 30000);
</script>
