<?php

namespace App\Filament\Resources\LessonQuestionResource\Pages;

use App\Filament\Resources\LessonQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLessonQuestion extends CreateRecord
{
    protected static string $resource = LessonQuestionResource::class;
}
