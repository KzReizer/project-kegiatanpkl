<?php

namespace Tests\Feature;

use App\Models\PklJournal;
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

        $response = $this->followingRedirects()->post('/jurnal', [
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
        PklJournal::create([
            'activity_date' => now()->toDateString(),
            'title' => 'Merapikan laporan',
            'category' => 'Dokumentasi',
            'description' => 'Menyusun bukti kegiatan.',
        ]);

        PklJournal::create([
            'activity_date' => now()->toDateString(),
            'title' => 'Bimbingan pembimbing',
            'category' => 'Bimbingan',
            'description' => 'Membahas evaluasi mingguan.',
        ]);

        $this->get('/?q=laporan')
            ->assertOk()
            ->assertSee('Merapikan laporan')
            ->assertDontSee('Bimbingan pembimbing');
    }

    public function test_user_can_update_journal_and_remove_selected_photo(): void
    {
        Storage::fake('public');

        $path = UploadedFile::fake()->image('lama.jpg')->store('pkl-photos', 'public');
        $journal = PklJournal::create([
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

        $response = $this->followingRedirects()->put("/jurnal/{$journal->id}", [
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
        $journal = PklJournal::create([
            'activity_date' => now()->subDay()->toDateString(),
            'title' => 'Input data harian',
            'category' => 'Kegiatan',
            'description' => 'Menginput data kegiatan.',
        ]);

        $this->post("/jurnal/{$journal->id}/duplicate")
            ->assertRedirect();

        $this->assertDatabaseHas('pkl_journals', [
            'title' => 'Salinan - Input data harian',
            'description' => 'Menginput data kegiatan.',
            'archived_at' => null,
        ]);

        $this->patch("/jurnal/{$journal->id}/archive")
            ->assertRedirect('/');

        $this->assertNotNull($journal->fresh()->archived_at);
    }

    public function test_export_page_downloads_csv(): void
    {
        PklJournal::create([
            'activity_date' => now()->toDateString(),
            'title' => 'Export kegiatan',
            'category' => 'Dokumentasi',
            'description' => 'Menyiapkan file laporan.',
        ]);

        $this->get('/export')
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_print_page_can_render_report(): void
    {
        PklJournal::create([
            'activity_date' => now()->toDateString(),
            'title' => 'Rekap kegiatan',
            'category' => 'Dokumentasi',
            'description' => 'Menyiapkan bahan laporan.',
        ]);

        $this->get('/laporan')
            ->assertOk()
            ->assertSee('Rekap Kegiatan Harian')
            ->assertSee('Rekap kegiatan');
    }

    public function test_homepage_shows_empty_state(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Catatan tidak ditemukan');
    }
}
