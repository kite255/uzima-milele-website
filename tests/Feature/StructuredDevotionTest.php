<?php

namespace Tests\Feature;

use App\Models\Devotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StructuredDevotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_devotions_have_structured_content_columns(): void
    {
        $this->assertTrue(
            Schema::hasColumns('devotions', [
                'feature_text',
                'lesson',
                'scripture_reference',
                'scripture_text',
                'ellen_white_quote',
                'ellen_white_reference',
            ])
        );
    }

    public function test_structured_devotion_page_displays_expected_sections(): void
    {
        $devotion = Devotion::query()->create([
            'title' => 'Nguvu ya Kusubiri kwa Imani',
            'slug' => 'nguvu-ya-kusubiri-kwa-imani',
            'content' => '<p>Legacy content</p>',
            'feature_text' => '<p>Ujumbe mkuu wa leo.</p>',
            'lesson' => '<p>Endelea kuamini Mungu.</p>',
            'scripture_reference' => 'Isaya 40:31',
            'scripture_text' => 'Bali wamngojeao Bwana watapata nguvu mpya.',
            'ellen_white_quote' => 'The watchman is to give the warning.',
            'ellen_white_reference' => 'Testimonies for the Church, vol. 9, p. 19.',
            'published_at' => now()->toDateString(),
        ]);

        $this->get(
            route('devotions.show', $devotion->slug)
        )
            ->assertOk()
            ->assertSee('Ujumbe wa Leo')
            ->assertSee('Funzo la Leo')
            ->assertSee('Isaya 40:31')
            ->assertSee('Bali wamngojeao Bwana watapata nguvu mpya.')
            ->assertSee('Ellen G. White');
    }
}
