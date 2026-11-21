<?php

namespace Tests\Feature;

use App\Models\Devotion;
use App\Models\Lesson;
use App\Models\LessonTopic;
use App\Models\Module;
use App\Models\WatotoVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobilePublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_devotion_routes_return_published_plain_text_and_exclude_future_content(): void
    {
        $this->travelTo(now('Africa/Dar_es_Salaam')->setDate(2026, 11, 21)->setTime(12, 0));
        $today = Devotion::create([
            'title' => 'Tumaini la Leo',
            'slug' => 'tumaini-la-leo',
            'content' => '<p>Mungu ni mwema.</p><p>Endelea kuamini.</p>',
            'published_at' => '2026-11-21',
        ]);
        $future = Devotion::create([
            'title' => 'Kesho',
            'slug' => 'kesho',
            'content' => '<p>Mafundisho.</p>',
            'published_at' => '2026-11-22',
        ]);

        $this->getJson('/api/mobile/v1/devotions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $today->slug);

        $this->getJson("/api/mobile/v1/devotions/{$today->slug}")
            ->assertOk()
            ->assertJsonPath('data.content', "Mungu ni mwema.\n\nEndelea kuamini.");
        $this->getJson("/api/mobile/v1/devotions/{$future->slug}")->assertNotFound();
    }

    public function test_lessons_only_list_public_courses_and_public_outline_items(): void
    {
        $lesson = Lesson::create([
            'title' => 'Masomo ya Biblia',
            'slug' => 'masomo-ya-biblia',
            'description' => '<p>Karibu kwenye kozi.</p>',
            'is_published' => true,
        ]);
        $draft = Lesson::create([
            'title' => 'Somo la Rasimu',
            'slug' => 'somo-la-rasimu',
            'is_published' => false,
        ]);
        $module = Module::create([
            'lesson_id' => $lesson->id,
            'title' => 'Maombi',
            'order' => 1,
            'is_published' => true,
        ]);
        Module::create([
            'lesson_id' => $lesson->id,
            'title' => 'Moduli ya Rasimu',
            'order' => 2,
            'is_published' => false,
        ]);
        foreach ([true, false] as $published) {
            LessonTopic::forceCreate([
                'module_id' => $module->id,
                'title' => $published ? 'Kuomba' : 'Mada ya Rasimu',
                'slug' => $published ? 'kuomba' : 'mada-rasimu',
                'order' => $published ? 1 : 2,
                'is_published' => $published,
            ]);
        }

        $this->getJson('/api/mobile/v1/lessons')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $lesson->slug);

        $this->getJson("/api/mobile/v1/lessons/{$lesson->slug}")
            ->assertOk()
            ->assertJsonPath('data.content', 'Karibu kwenye kozi.')
            ->assertJsonCount(1, 'data.modules')
            ->assertJsonCount(1, 'data.modules.0.topics')
            ->assertJsonPath('data.modules.0.topics.0.slug', 'kuomba');

        $this->getJson("/api/mobile/v1/lessons/{$draft->slug}")->assertNotFound();
    }

    public function test_children_routes_only_return_published_stories_with_trusted_video_urls(): void
    {
        foreach ([true, false] as $published) {
            WatotoVideo::create([
                'title' => $published ? 'Hadithi ya Biblia' : 'Hadithi ya Rasimu',
                'slug' => $published ? 'hadithi' : 'rasimu',
                'youtube_url' => 'https://youtu.be/AbCdeFgHijk',
                'description' => '<p>Hadithi fupi.</p>',
                'is_published' => $published,
            ]);
        }

        $this->getJson('/api/mobile/v1/children')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'hadithi');

        $this->getJson('/api/mobile/v1/children/hadithi')
            ->assertOk()
            ->assertJsonPath('data.content', 'Hadithi fupi.')
            ->assertJsonPath('data.youtube_url', 'https://www.youtube.com/watch?v=AbCdeFgHijk');

        $this->getJson('/api/mobile/v1/children/rasimu')->assertNotFound();

        WatotoVideo::create([
            'title' => 'Hadithi Nyingine',
            'slug' => 'hadithi-nyingine',
            'youtube_url' => 'https://youtube.com.example/watch?v=AbCdeFgHijk',
            'is_published' => true,
        ]);

        $this->getJson('/api/mobile/v1/children/hadithi-nyingine')
            ->assertOk()
            ->assertJsonPath('data.youtube_url', null)
            ->assertJsonPath('data.thumbnail_url', null);
    }
}
