<?php

namespace App\Filament\Resources\WatotoQuizResource\Pages;

use App\Filament\Resources\WatotoQuizResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWatotoQuiz extends EditRecord
{
    protected static string $resource = WatotoQuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}