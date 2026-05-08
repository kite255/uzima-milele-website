<?php

namespace App\Filament\Resources\WatotoQuizResource\Pages;

use App\Filament\Resources\WatotoQuizResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWatotoQuizzes extends ListRecords
{
    protected static string $resource = WatotoQuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}