<?php

namespace Tests\Feature;

use App\Models\PklJournal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PklJournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_pkl_journal_with_multiple_photos(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->followingRedirects()->post('/jurnal', [
            'activity_date' => now()->toDateString(),
            'title' => 'Membuat halaman laporan',
            'location' => 'Ruang IT',
            'category' => 'Kegiatan',
            'description' => 'Mengumpulkan dokumentasi dan menulis catatan kegiatan harian.',
            'learning' => 'Belajar menyusun catatan kerja.',
            'photos' => [
                UploadedFile::fake()->image('dokumentasi-1.jpg'),
                UploadedFile::fake()->image('dokumentasi-2.jpg'),
            ],
        ]);

        $response
            ->assertOk()
            ->assertSee('Membuat halaman laporan')
            ->assertSee('Mengumpulkan dokumentasi dan menulis catatan kegiatan harian.');

        $this->assertDatabaseHas('pkl_journals', [
            'user_id' => $user->id,
            'title' => 'Membuat halaman laporan',
            'location' => 'Ruang IT',
            'category' => 'Kegiatan',
            'description' => 'Mengumpulkan dokumentasi dan menulis catatan kegiatan harian.',
        ]);

        $journal = PklJournal::with('photos')->firstOrFail();

        $this->assertCount(2, $journal->photos);
        Storage::disk('public')->assertExists($journal->photos->first()->path);
        $this->assertSame($journal->photos->first()->path, $journal->photo_path);
    }

    public function test_user_can_search_journals(): void
    {
        $user = User::factory()->create();

        PklJournal::create([
            'user_id' => $user->id,
            'activity_date' => now()->toDateString(),
            'title' => 'Merapikan laporan',
            'category' => 'Dokumentasi',
            'description' => 'Menyusun bukti kegiatan.',
        ]);

        PklJournal::create([
            'user_id' => $user->id,
            'activity_date' => now()->toDateString(),
            'title' => 'Bimbingan pembimbing',
            'category' => 'Bimbingan',
            'description' => 'Membahas evaluasi mingguan.',
        ]);

        $this->actingAs($user)->get(route('journals.index', ['q' => 'laporan']))
            ->assertOk()
            ->assertSee('Merapikan laporan')
            ->assertDontSee('Bimbingan pembimbing');
    }

    public function test_user_can_update_journal_and_remove_selected_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $path = UploadedFile::fake()->image('lama.jpg')->store('pkl-photos', 'public');
        $journal = PklJournal::create([
            'user_id' => $user->id,
            'activity_date' => now()->toDateString(),
            'title' => 'Judul lama',
            'category' => 'Kegiatan',
            'description' => 'Catatan lama',
            'photo_path' => $path,
            'photo_original_name' => 'lama.jpg',
        ]);
        $photo = $journal->photos()->create([
            'path' => $path,
            'original_name' => 'lama.jpg',
        ]);

        $response = $this->actingAs($user)->followingRedirects()->put("/jurnal/{$journal->id}", [
            'activity_date' => now()->toDateString(),
            'title' => 'Judul baru',
            'category' => 'Selesai',
            'description' => 'Catatan baru',
            'remove_photo_ids' => [$photo->id],
        ]);

        $response
            ->assertOk()
            ->assertSee('Judul baru');

        $this->assertDatabaseHas('pkl_journals', [
            'id' => $journal->id,
            'title' => 'Judul baru',
            'category' => 'Selesai',
            'photo_path' => null,
        ]);

        $this->assertDatabaseMissing('pkl_journal_photos', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_user_can_duplicate_and_archive_journal(): void
    {
        $user = User::factory()->create();
        $journal = PklJournal::create([
            'user_id' => $user->id,
            'activity_date' => now()->subDay()->toDateString(),
            'title' => 'Input data harian',
            'category' => 'Kegiatan',
            'description' => 'Menginput data kegiatan.',
        ]);

        $this->actingAs($user)->post("/jurnal/{$journal->id}/duplicate")
            ->assertRedirect();

        $this->assertDatabaseHas('pkl_journals', [
            'user_id' => $user->id,
            'title' => 'Salinan - Input data harian',
            'description' => 'Menginput data kegiatan.',
            'archived_at' => null,
        ]);

        $this->actingAs($user)->patch("/jurnal/{$journal->id}/archive")
            ->assertRedirect(route('journals.index'));

        $this->assertNotNull($journal->fresh()->archived_at);
    }

    public function test_export_page_downloads_csv(): void
    {
        $user = User::factory()->create();
        PklJournal::create([
            'user_id' => $user->id,
            'activity_date' => now()->toDateString(),
            'title' => 'Export kegiatan',
            'category' => 'Dokumentasi',
            'description' => 'Menyiapkan file laporan.',
        ]);

        $this->actingAs($user)->get('/export')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_print_page_can_render_report(): void
    {
        $user = User::factory()->create();
        PklJournal::create([
            'user_id' => $user->id,
            'activity_date' => now()->toDateString(),
            'title' => 'Rekap kegiatan',
            'category' => 'Dokumentasi',
            'description' => 'Menyiapkan bahan laporan.',
        ]);

        $this->actingAs($user)->get('/laporan')
            ->assertOk()
            ->assertSee('Rekap Kegiatan Harian')
            ->assertSee('Rekap kegiatan');
    }

    public function test_homepage_shows_empty_state(): void
    {
        $this->actingAs(User::factory()->create())->get(route('journals.index'))
            ->assertOk()
            ->assertSee('Catatan tidak ditemukan');
    }

    public function test_admin_can_open_account_report_detail(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['name' => 'Siswa PKL']);

        PklJournal::create([
            'user_id' => $student->id,
            'activity_date' => now()->toDateString(),
            'title' => 'Laporan siswa',
            'category' => 'Kegiatan',
            'description' => 'Isi laporan siswa.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Siswa PKL');

        $this->actingAs($admin)
            ->get(route('admin.users.show', $student))
            ->assertOk()
            ->assertSee('Laporan siswa');
    }

    public function test_non_admin_cannot_open_admin_pages(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }
}
