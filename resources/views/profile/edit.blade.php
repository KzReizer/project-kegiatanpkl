<x-app-layout>
    <div class="content-shell profile-shell">
        <section class="page-header compact-header">
            <div>
                <p class="eyebrow">Pengaturan akun</p>
                <h1>Profile</h1>
                <p class="subtle-text">Kelola identitas, keamanan, dan preferensi akun Jurnal PKL Anda.</p>
            </div>
        </section>

        <section class="profile-grid">
            <article class="profile-card">
                @include('profile.partials.update-profile-information-form')
            </article>

            <article class="profile-card">
                @include('profile.partials.update-password-form')
            </article>

            <article class="profile-card danger-zone">
                @include('profile.partials.delete-user-form')
            </article>
        </section>
    </div>
</x-app-layout>
