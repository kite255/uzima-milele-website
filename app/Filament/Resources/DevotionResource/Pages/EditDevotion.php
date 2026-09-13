<?php

namespace App\Filament\Resources\DevotionResource\Pages;

use App\Filament\Resources\DevotionResource;
use App\Models\Devotion;
use App\Services\DevotionEmailAutomationService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDevotion extends EditRecord
{
    protected static string $resource = DevotionResource::class;

    protected function afterSave(): void
    {
        /** @var Devotion $devotion */
        $devotion = $this->record;

        app(
            DevotionEmailAutomationService::class
        )->sync(
            $devotion
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}