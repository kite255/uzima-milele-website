<?php

namespace Tests\Unit;

use App\Models\Devotion;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DevotionImageUrlTest extends TestCase
{
    public function test_it_returns_public_url_for_an_existing_devotion_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('devotions/finch.jpg', 'image-bytes');

        $devotion = new Devotion([
            'image' => 'devotions/finch.jpg',
        ]);

        $this->assertSame(
            Storage::disk('public')->url('devotions/finch.jpg'),
            $devotion->image_url
        );
    }

    public function test_it_returns_null_when_the_stored_devotion_image_is_missing(): void
    {
        Storage::fake('public');

        $devotion = new Devotion([
            'image' => 'devotions/missing.jpg',
        ]);

        $this->assertNull($devotion->image_url);
    }
}
