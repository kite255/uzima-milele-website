<?php

namespace App\Filament\Resources\LessonResource\Pages;

use App\Filament\Resources\LessonResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLesson extends EditRecord
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewPublic')
                ->label('View Public Page')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => route('lessons.show', $this->record->slug))
                ->openUrlInNewTab(),

            Actions\DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Lesson updated successfully';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', [
            'record' => $this->record,
        ]);
    }
}