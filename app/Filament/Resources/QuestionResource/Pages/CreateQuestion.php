<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected function beforeCreate(): void
    {
        QuestionResource::validateQuestionOptions($this->form->getState());
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Question created successfully.')
            ->success()
            ->send();
    }
}