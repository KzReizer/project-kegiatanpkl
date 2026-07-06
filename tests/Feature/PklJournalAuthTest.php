<?php

namespace Tests\Feature;

use App\Models\PklJournal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PklJournalAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $response = $this->get(route('journals.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_only_sees_their_own_journals(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        PklJournal::create([
            'user_id' => $owner->id,
            'activity_date' => now()->toDateString(),
            'title' => 'Kegiatan milik owner',
            'description' => 'Isi owner',
            'category' => 'Kegiatan',
        ]);

        PklJournal::create([
            'user_id' => $otherUser->id,
            'activity_date' => now()->toDateString(),
            'title' => 'Kegiatan milik orang lain',
            'description' => 'Isi orang lain',
            'category' => 'Kegiatan',
        ]);

        $response = $this->actingAs($owner)->get(route('journals.index'));

        $response->assertOk();
        $response->assertSee('Kegiatan milik owner');
        $response->assertDontSee('Kegiatan milik orang lain');
    }
}
