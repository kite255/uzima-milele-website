<?php

namespace App\Filament\Resources\LessonQuestionResource\Pages;

use App\Filament\Resources\LessonQuestionResource;
use App\Notifications\LessonQuestionAnsweredNotification;
use Filament\Actions;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\EditRecord;

class EditLessonQuestion extends EditRecord
{
    protected static string $resource = LessonQuestionResource::class;

    protected $shouldNotifyStudent = false;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $oldAnswer = trim((string) $this->record->answer);
        $newAnswer = trim((string) ($data['answer'] ?? ''));

        if ($newAnswer !== '') {
            $data['answer'] = $newAnswer;
            $data['answered_by'] = auth()->id();

            if (empty($data['answered_at'])) {
                $data['answered_at'] = now();
            }

            $this->shouldNotifyStudent = $oldAnswer === '' || $oldAnswer !== $newAnswer;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if (! $this->shouldNotifyStudent) {
            return;
        }

        $this->record->loadMissing(['user', 'lesson']);

        if (! $this->record->user) {
            return;
        }

        if (empty($this->record->answer)) {
            return;
        }

        $this->record->user->notify(
            new LessonQuestionAnsweredNotification($this->record)
        );

        FilamentNotification::make()
            ->title('Student notified')
            ->body('The student has been notified by website notification and email.')
            ->success()
            ->send();
    }
}