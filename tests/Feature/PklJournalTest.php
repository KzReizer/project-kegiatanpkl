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
            'description' => 'Mengumpulkan dokumentasi dan menulis catatan kegiatan harian.',
            'photo' => UploadedFile::fake()->image('dokumentasi.jpg'),
        ]);

        $response
            ->assertOk()
            ->assertSee('Membuat halaman laporan')
            ->assertSee('Mengumpulkan dokumentasi dan menulis catatan kegiatan harian.');

        $this->assertDatabaseHas('pkl_journals', [
            'title' => 'Membuat halaman laporan',
            'description' => 'Mengumpulkan dokumentasi dan menulis catatan kegiatan harian.',
        ]);

        $journal = PklJournal::firstOrFail();

        Storage::disk('public')->assertExists($journal->photo_path);
    }

    public function test_homepage_shows_empty_state(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Belum ada catatan PKL');
    }
}
