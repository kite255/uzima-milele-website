<?php

namespace App\Filament\Resources\LessonEnrollmentResource\Pages;

use App\Filament\Resources\LessonEnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLessonEnrollment extends EditRecord
{
    protected static string $resource = LessonEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
