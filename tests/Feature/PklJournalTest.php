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

    public function test_user_can_create_pkl_journal_with_photo(): void
    {
        Storage::fake('public');

        $response = $this->followingRedirects()->post('/jurnal', [
            'activity_date' => now()->toDateString(),
            'title' => 'Membuat halaman laporan',
            'location' => 'Ruang IT',
            'category' => 'Kegiatan',
            'description' => 'Mengumpulkan dokumentasi dan menulis catatan kegiatan harian.',
            'learning' => 'Belajar menyusun catatan kerja.',
            'photo' => UploadedFile::fake()->image('dokumentasi.jpg'),
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

        $journal = PklJournal::firstOrFail();

        Storage::disk('public')->assertExists($journal->photo_path);
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

    public function test_user_can_update_journal_and_remove_photo(): void
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

        $response = $this->followingRedirects()->put("/jurnal/{$journal->id}", [
            'activity_date' => now()->toDateString(),
            'title' => 'Judul baru',
            'category' => 'Selesai',
            'description' => 'Catatan baru',
            'remove_photo' => '1',
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

        Storage::disk('public')->assertMissing($path);
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
