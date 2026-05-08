<?php

namespace App\Filament\Resources\LessonQuestionResource\Pages;

use App\Filament\Resources\LessonQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLessonQuestions extends ListRecords
{
    protected static string $resource = LessonQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
