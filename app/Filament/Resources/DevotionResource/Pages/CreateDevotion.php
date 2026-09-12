<?php

namespace App\Filament\Resources\DevotionResource\Pages;

use App\Filament\Resources\DevotionResource;
use App\Models\Devotion;
use App\Services\DevotionEmailAutomationService;
use Filament\Resources\Pages\CreateRecord;

class CreateDevotion extends CreateRecord
{
    protected static string $resource = DevotionResource::class;

    protected function afterCreate(): void
    {
        /** @var Devotion $devotion */
        $devotion = $this->record;

        app(
            DevotionEmailAutomationService::class
        )->sync(
            $devotion
        );
    }
}