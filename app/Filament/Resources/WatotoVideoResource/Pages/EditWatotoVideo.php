<?php

namespace App\Filament\Resources\WatotoVideoResource\Pages;

use App\Filament\Resources\WatotoVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWatotoVideo extends EditRecord
{
    protected static string $resource = WatotoVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
