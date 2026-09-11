<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonEnrollmentResource\Pages;
use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LessonEnrollmentResource extends Resource
{
    protected static ?string $model = LessonEnrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Lesson Management';

    protected static ?string $navigationLabel = 'Student Enrollments';

    protected static ?string $modelLabel = 'Student Enrollment';

    protected static ?string $pluralModelLabel = 'Student Enrollments';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Enrollment Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Student')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('lesson_id')
                            ->label('Lesson')
                            ->relationship('lesson', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('follow_up_instructor_id')
                            ->label('Follow-up Instructor')
                            ->options(
                                fn (Forms\Get $get): array =>
                                    static::eligibleInstructorOptions(
                                        $get('lesson_id')
                                            ? (int) $get('lesson_id')
                                            : null
                                    )
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Unassigned')
                            ->visible(
                                fn (): bool =>
                                    auth()->user()?->role === 'admin'
                            )
                            ->helperText(
                                'Admin can manually assign or reassign the student to an eligible instructor.'
                            ),

                        Forms\Components\DateTimePicker::make('enrolled_at')
                            ->label('Enrolled At')
                            ->seconds(false)
                            ->default(now()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('followUpInstructor.name')
                    ->label('Follow-up Instructor')
                    ->placeholder('Unassigned')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                /*
                |--------------------------------------------------------------------------
                | Learning Progress
                |--------------------------------------------------------------------------
                |
                | This is topic progress only.
                | It does NOT automatically mean that the whole lesson
                | is complete.
                |
                */
                Tables\Columns\TextColumn::make('progress')
                    ->label('Learning Progress')
                    ->state(function (LessonEnrollment $record): string {
                        return $record->learning_progress_percent . '%';
                    })
                    ->badge()
                    ->color(function (LessonEnrollment $record): string {
                        $progress = $record->learning_progress_percent;

                        return match (true) {
                            $record->is_completed => 'success',
                            $progress >= 100 => 'warning',
                            $progress >= 50 => 'warning',
                            default => 'gray',
                        };
                    }),

                /*
                |--------------------------------------------------------------------------
                | Completion Status
                |--------------------------------------------------------------------------
                |
                | This is now the real lesson completion state.
                |
                | It can show:
                | - In Progress
                | - Module Quiz Pending
                | - Final Quiz Pending
                | - Completed
                |
                */
                Tables\Columns\TextColumn::make('completion_status_display')
                    ->label('Status')
                    ->state(function (LessonEnrollment $record): string {
                        return match ($record->completion_status) {
                            'completed' => 'Completed',
                            'module_quiz_pending' => 'Module Quiz Pending',
                            'final_quiz_pending' => 'Final Quiz Pending',
                            'in_progress' => 'In Progress',
                            'not_started' => 'Not Started',
                            default => $record->completion_label,
                        };
                    })
                    ->badge()
                    ->color(function (LessonEnrollment $record): string {
                        return match ($record->completion_status) {
                            'completed' => 'success',
                            'module_quiz_pending' => 'warning',
                            'final_quiz_pending' => 'warning',
                            'in_progress' => 'info',
                            'not_started' => 'gray',
                            default => 'gray',
                        };
                    }),

                /*
                |--------------------------------------------------------------------------
                | Modules
                |--------------------------------------------------------------------------
                */
                Tables\Columns\TextColumn::make('modules_progress')
                    ->label('Modules')
                    ->state(function (LessonEnrollment $record): string {
                        return $record->completed_modules
                            . '/'
                            . $record->total_modules;
                    })
                    ->badge()
                    ->color(function (LessonEnrollment $record): string {
                        if ($record->is_completed) {
                            return 'success';
                        }

                        if ($record->modules_with_quiz_pending > 0) {
                            return 'warning';
                        }

                        return 'gray';
                    })
                    ->toggleable(),

                /*
                |--------------------------------------------------------------------------
                | Quiz Pending
                |--------------------------------------------------------------------------
                */
                Tables\Columns\TextColumn::make('quiz_status')
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
                                : "{$count} Module Quizzes Pending";
                        }

                        return 'No Quiz Pending';
                    })
                    ->badge()
                    ->color(function (LessonEnrollment $record): string {
                        if ($record->is_completed) {
                            return 'success';
                        }

                        if ($record->has_any_quiz_pending) {
                            return 'warning';
                        }

                        return 'gray';
                    })
                    ->toggleable(),

                /*
                |--------------------------------------------------------------------------
                | Certificate
                |--------------------------------------------------------------------------
                */
                Tables\Columns\TextColumn::make('certificate_status')
                    ->label('Certificate')
                    ->state(function (LessonEnrollment $record): string {
                        $hasCertificate = Certificate::query()
                            ->where('user_id', $record->user_id)
                            ->where('lesson_id', $record->lesson_id)
                            ->exists();

                        if ($hasCertificate) {
                            return 'Issued';
                        }

                        if ($record->is_completed) {
                            return 'Ready';
                        }

                        return 'Not Eligible';
                    })
                    ->badge()
                    ->color(function (LessonEnrollment $record): string {
                        $hasCertificate = Certificate::query()
                            ->where('user_id', $record->user_id)
                            ->where('lesson_id', $record->lesson_id)
                            ->exists();

                        if ($hasCertificate) {
                            return 'success';
                        }

                        if ($record->is_completed) {
                            return 'info';
                        }

                        return 'gray';
                    }),

                /*
                |--------------------------------------------------------------------------
                | Schedule Status
                |--------------------------------------------------------------------------
                */
                Tables\Columns\TextColumn::make('schedule_status_label')
                    ->label('Schedule')
                    ->badge()
                    ->color(
                        fn (LessonEnrollment $record): string =>
                            $record->schedule_status_color
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make('target_completion_date')
                    ->label('Target Date')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('remaining_days_label')
                    ->label('Time Remaining')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('enrolled_at')
                    ->label('Enrolled At')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lesson_id')
                    ->label('Lesson')
                    ->relationship('lesson', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Student')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('follow_up_instructor_id')
                    ->label('Follow-up Instructor')
                    ->relationship(
                        'followUpInstructor',
                        'name',
                        modifyQueryUsing: fn (Builder $query) =>
                            $query->where(
                                'role',
                                'instructor'
                            )
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('reassignInstructor')
                    ->label('Reassign Instructor')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'admin'
                    )
                    ->form(
                        fn (LessonEnrollment $record): array => [
                            Forms\Components\Select::make(
                                'follow_up_instructor_id'
                            )
                                ->label('Follow-up Instructor')
                                ->options(
                                    static::eligibleInstructorOptions(
                                        $record->lesson_id
                                    )
                                )
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->placeholder('Unassigned')
                                ->default(
                                    $record->follow_up_instructor_id
                                ),
                        ]
                    )
                    ->action(
                        function (
                            LessonEnrollment $record,
                            array $data
                        ): void {
                            $instructorId =
                                $data['follow_up_instructor_id']
                                ?? null;

                            $record->forceFill([
                                'follow_up_instructor_id' =>
                                    $instructorId,
                                'instructor_assigned_at' =>
                                    $instructorId
                                        ? now()
                                        : null,
                            ])->save();
                        }
                    ),

                Tables\Actions\Action::make('viewStudent')
                    ->label('View Student')
                    ->icon('heroicon-o-user')
                    ->url(
                        fn (LessonEnrollment $record) =>
                            url(
                                '/admin/users/'
                                . $record->user_id
                                . '/edit'
                            )
                    )
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('viewLesson')
                    ->label('View Lesson')
                    ->icon('heroicon-o-academic-cap')
                    ->url(function (LessonEnrollment $record) {
                        if (! $record->lesson) {
                            return null;
                        }

                        return route(
                            'lessons.show',
                            $record->lesson->slug
                        );
                    })
                    ->openUrlInNewTab(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(
                'enrolled_at',
                'desc'
            );
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'user',
                'followUpInstructor',
                'lesson.modules.topics',
                'lesson.modules.quizzes',
                'lesson.finalQuiz',
                'lesson.leadInstructor',
                'lesson.followUpInstructors',
            ]);

        $user = auth()->user();

        if (
            $user
            && $user->role === 'instructor'
        ) {
            return $query->where(
                function (Builder $enrollmentQuery) use ($user) {
                    $enrollmentQuery
                        ->whereHas(
                            'lesson',
                            fn (Builder $lessonQuery) =>
                                $lessonQuery->where(
                                    'lead_instructor_id',
                                    $user->id
                                )
                        )
                        ->orWhere(
                            'follow_up_instructor_id',
                            $user->id
                        )
                        ->orWhereHas(
                            'lesson',
                            function (Builder $lessonQuery) use ($user) {
                                $lessonQuery
                                    ->where(
                                        'instructor_id',
                                        $user->id
                                    )
                                    ->whereNull(
                                        'lead_instructor_id'
                                    )
                                    ->whereDoesntHave(
                                        'followUpInstructors'
                                    );
                            }
                        );
                }
            );
        }

        return $query;
    }

    public static function eligibleInstructorOptions(
        ?int $lessonId
    ): array {
        if (! $lessonId) {
            return [];
        }

        $lesson = Lesson::query()
            ->with([
                'leadInstructor',
                'followUpInstructors',
            ])
            ->find($lessonId);

        if (! $lesson) {
            return [];
        }

        $instructors = $lesson
            ->followUpInstructors
            ->where('role', 'instructor');

        if (
            $lesson->lead_can_receive_students
            && $lesson->leadInstructor
            && $lesson->leadInstructor->role === 'instructor'
        ) {
            $instructors = $instructors->push(
                $lesson->leadInstructor
            );
        }

        return $instructors
            ->unique('id')
            ->sortBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                Pages\ListLessonEnrollments::route('/'),

            'create' =>
                Pages\CreateLessonEnrollment::route('/create'),

            'edit' =>
                Pages\EditLessonEnrollment::route('/{record}/edit'),
        ];
    }
}
