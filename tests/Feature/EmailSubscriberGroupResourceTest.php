<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailSubscriberGroupResource;
use App\Models\EmailSubscriberGroup;
use Tests\TestCase;

class EmailSubscriberGroupResourceTest extends TestCase
{
    public function test_resource_uses_email_subscriber_group_model(): void
    {
        $this->assertSame(
            EmailSubscriberGroup::class,
            EmailSubscriberGroupResource::getModel()
        );
    }

    public function test_resource_has_expected_pages(): void
    {
        $pages = EmailSubscriberGroupResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }
}