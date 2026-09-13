<?php

namespace Tests\Feature;

use App\Filament\Resources\DevotionResource\Pages\CreateDevotion;
use App\Models\EmailSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StructuredDevotionFilamentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_structured_devotion_from_filament(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        EmailSubscriber::query()->create([
            'name' => 'Test Subscriber',
            'email' => 'subscriber@example.com',
            'status' => 'subscribed',
            'subscribed_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(CreateDevotion::class)
            ->fillForm([
                'title' => 'Nguvu ya Kusubiri kwa Imani',
                'slug' => 'nguvu-ya-kusubiri-kwa-imani',

                'feature_text' =>
                    '<p>Ujumbe mkuu wa leo.</p>',

                'lesson' =>
                    '<p>Endelea kuamini Mungu.</p>',

                'scripture_reference' =>
                    'Isaya 40:31',

                'scripture_text' =>
                    'Bali wamngojeao Bwana watapata nguvu mpya.',

                'ellen_white_quote' =>
                    'The watchman is to give the warning.',

                'ellen_white_reference' =>
                    'Testimonies for the Church, vol. 9, p. 19.',

                'published_at' =>
                    now()->addDay()->toDateString(),

                'email_send_time' =>
                    '06:00',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('devotions', [
            'slug' =>
                'nguvu-ya-kusubiri-kwa-imani',

            'scripture_reference' =>
                'Isaya 40:31',

            'email_send_time' =>
                '06:00',
        ]);
    }
}
