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

        $this->assertSame('devotions/finch.jpg', $devotion->image);
        $this->assertSame(
            Storage::disk('public')->url('devotions/finch.jpg'),
            $devotion->image_url
        );
    }

    public function test_it_hides_a_missing_devotion_image_from_views(): void
    {
        Storage::fake('public');

        $devotion = new Devotion([
            'image' => 'devotions/missing.jpg',
        ]);

        $this->assertNull($devotion->image);
        $this->assertNull($devotion->image_url);
    }

    public function test_it_normalizes_legacy_storage_prefixes(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('devotions/finch.jpg', 'image-bytes');

        $devotion = new Devotion([
            'image' => '/storage/devotions/finch.jpg',
        ]);

        $this->assertSame('devotions/finch.jpg', $devotion->image);
        $this->assertSame(
            Storage::disk('public')->url('devotions/finch.jpg'),
            $devotion->image_url
        );
    }
}
