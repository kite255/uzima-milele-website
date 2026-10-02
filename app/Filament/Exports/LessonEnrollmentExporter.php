<?php

namespace App\Filament\Exports;

use App\Models\Certificate;
use App\Models\LessonEnrollment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class LessonEnrollmentExporter extends Exporter
{
    protected static ?string $model = LessonEnrollment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('user.name')
                ->label('Student Name'),

            ExportColumn::make('user.email')
                ->label('Email'),

            ExportColumn::make('lesson.title')
                ->label('Lesson'),

            ExportColumn::make('followUpInstructor.name')
                ->label('Follow-up Instructor'),

            ExportColumn::make('learning_progress')
                ->label('Learning Progress')
                ->state(
                    fn (LessonEnrollment $record): string =>
                        $record->learning_progress_percent . '%'
                ),

            ExportColumn::make('completion_status_display')
                ->label('Completion Status')
                ->state(function (LessonEnrollment $record): string {
                    return match ($record->completion_status) {
                        'completed' => 'Completed',
                        'module_quiz_pending' => 'Module Quiz Pending',
                        'final_quiz_pending' => 'Final Quiz Pending',
                        'in_progress' => 'In Progress',
                        'not_started' => 'Not Started',
                        default => $record->completion_label,
                    };
                }),

            ExportColumn::make('modules_progress')
                ->label('Modules Progress')
                ->state(
                    fn (LessonEnrollment $record): string =>
                        $record->completed_modules . '/' . $record->total_modules
                ),

            ExportColumn::make('quiz_status')
                ->label('Quiz Status')
                ->state(function (LessonEnrollment $record): string {
                    if ($record->is_completed) {
                        return 'Complete';
                    }

                    if ($record->has_final_quiz_pending) {
                        return 'Final Quiz Pending';
                    }

                    if ($record->has_module_quiz_pending) {
                        $count = $record->modules_with_quiz_pending;

                        return $count === 1
                            ? '1 Module Quiz Pending'
                            : $count . ' Module Quizzes Pending';
                    }

                    return 'No Quiz Pending';
                }),

            ExportColumn::make('certificate_status')
                ->label('Certificate Status')
                ->state(function (LessonEnrollment $record): string {
                    $hasCertificate = Certificate::query()
                        ->where('user_id', $record->user_id)
                        ->where('lesson_id', $record->lesson_id)
                        ->exists();

                    if ($hasCertificate) {
                        return 'Issued';
                    }

                    return $record->is_completed
                        ? 'Ready'
                        : 'Not Eligible';
                }),

            ExportColumn::make('enrolled_at')
                ->label('Enrolled At')
                ->formatStateUsing(
                    fn ($state): string =>
                        $state?->format('d M Y, H:i') ?? ''
                ),

            ExportColumn::make('target_completion_date')
                ->label('Target Completion Date')
                ->formatStateUsing(
                    fn ($state): string =>
                        $state?->format('d M Y') ?? ''
                ),

            ExportColumn::make('remaining_days_label')
                ->label('Time Remaining'),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with([
            'user',
            'lesson',
            'followUpInstructor',
        ]);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your student enrollment export has completed and '
            . number_format($export->successful_rows)
            . ' '
            . str('row')->plural($export->successful_rows)
            . ' exported.';

        $failedRowsCount = $export->getFailedRowsCount();

        if ($failedRowsCount > 0) {
            $body .= ' '
                . number_format($failedRowsCount)
                . ' '
                . str('row')->plural($failedRowsCount)
                . ' failed to export.';
        }

        return $body;
    }
}
