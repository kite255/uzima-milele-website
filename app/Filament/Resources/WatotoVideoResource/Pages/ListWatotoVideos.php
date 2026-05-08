<?php

namespace App\Filament\Resources\WatotoVideoResource\Pages;

use App\Filament\Resources\WatotoVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWatotoVideos extends ListRecords
{
    protected static string $resource = WatotoVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
