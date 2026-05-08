<?php

namespace App\Filament\Resources\LessonTopicResource\Pages;

use App\Filament\Resources\LessonTopicResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLessonTopics extends ListRecords
{
    protected static string $resource = LessonTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
