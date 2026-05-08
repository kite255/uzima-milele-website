<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected function beforeSave(): void
    {
        QuestionResource::validateQuestionOptions($this->form->getState());
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Question updated successfully.')
            ->success()
            ->send();
    }
}