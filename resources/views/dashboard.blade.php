<x-app-layout>
    <div class="content-shell">
        <section class="page-header compact-header">
            <div>
                <p class="eyebrow">Dashboard</p>
                <h1>Workspace PKL</h1>
                <p class="subtle-text">Lanjutkan pencatatan kegiatan harian dan dokumentasi PKL Anda.</p>
            </div>
        </section>

        <!-- Attendance Check-In Card -->
        @include('attendances._card-checkin')

        <div style="margin-top: 2rem;"></div>

        <div class="profile-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Akses cepat</p>
                    <h2>Jurnal PKL</h2>
                </div>
                <span class="card-icon"><i data-lucide="book-open-text"></i></span>
            </div>

            <div class="form-actions" style="margin-top: 1rem;">
                <a class="primary-button" href="{{ route('journals.index') }}">
                    <i data-lucide="arrow-right"></i>
                    Buka jurnal PKL
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
