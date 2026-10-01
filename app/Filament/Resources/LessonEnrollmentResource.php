<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonEnrollmentResource\Pages;
use App\Models\Certificate;
use App\Models\Lesson;
use App\Models\LessonEnrollment;
use App\Models\User;
use App\Services\FollowUpInstructorAssignmentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

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
                            ->label('Current Follow-up Instructor')
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
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(
                                fn (): bool =>
                                    auth()->user()?->role === 'admin'
                            )
                            ->helperText(
                                'Use the Assign / Reassign Instructor action to choose Automatic or Manual assignment.'
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
                            $query->where('role', 'instructor')
                    )
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('assignInstructor')
                    ->label('Assign / Reassign Instructor')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'admin'
                    )
                    ->form(
                        fn (LessonEnrollment $record): array => [
                            Forms\Components\Select::make('assignment_mode')
                                ->label('Assignment Mode')
                                ->options([
                                    'automatic' => 'Automatic — choose the least-loaded eligible instructor',
                                    'manual' => 'Manual — choose an instructor yourself',
                                ])
                                ->default('automatic')
                                ->required()
                                ->live(),

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
                                ->placeholder('Choose an instructor')
                                ->default(
                                    $record->follow_up_instructor_id
                                )
                                ->visible(
                                    fn (Forms\Get $get): bool =>
                                        $get('assignment_mode') === 'manual'
                                )
                                ->required(
                                    fn (Forms\Get $get): bool =>
                                        $get('assignment_mode') === 'manual'
                                ),
                        ]
                    )
                    ->action(
                        function (
                            LessonEnrollment $record,
                            array $data
                        ): void {
                            $service = app(
                                FollowUpInstructorAssignmentService::class
                            );

                            if (
                                ($data['assignment_mode'] ?? 'automatic')
                                === 'automatic'
                            ) {
                                $assigned = $service
                                    ->assignAutomatically(
                                        $record,
                                        reassign: true
                                    );
                            } else {
                                $instructor = User::query()->findOrFail(
                                    (int) $data['follow_up_instructor_id']
                                );

                                $assigned = $service->assignManually(
                                    $record,
                                    $instructor
                                );
                            }

                            if (! $assigned) {
                                Notification::make()
                                    ->title('No eligible instructor available')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Follow-up instructor assigned')
                                ->body(
                                    $record->user?->name
                                    . ' is assigned to '
                                    . $assigned->name
                                    . '.'
                                )
                                ->success()
                                ->send();
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
                    Tables\Actions\BulkAction::make('assignStudentsToInstructor')
                        ->label('Assign Students to Instructor')
                        ->icon('heroicon-o-user-group')
                        ->color('warning')
                        ->visible(
                            fn (): bool =>
                                auth()->user()?->role === 'admin'
                        )
                        ->form([
                            Forms\Components\Select::make(
                                'follow_up_instructor_id'
                            )
                                ->label('Follow-up Instructor')
                                ->options(
                                    fn (Collection $records): array =>
                                        static::eligibleInstructorOptionsForEnrollments(
                                            $records
                                        )
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText(
                                    'Only instructors eligible for every selected student are shown.'
                                ),
                        ])
                        ->action(
                            function (
                                Collection $records,
                                array $data
                            ): void {
                                $instructor = User::query()->findOrFail(
                                    (int) $data['follow_up_instructor_id']
                                );

                                try {
                                    $changed = app(
                                        FollowUpInstructorAssignmentService::class
                                    )->assignManyManually(
                                        $records,
                                        $instructor
                                    );
                                } catch (InvalidArgumentException $exception) {
                                    Notification::make()
                                        ->title('Students could not be assigned')
                                        ->body($exception->getMessage())
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                Notification::make()
                                    ->title('Students assigned successfully')
                                    ->body(
                                        $changed === 1
                                            ? '1 student was assigned to ' . $instructor->name . '.'
                                            : $changed . ' students were assigned to ' . $instructor->name . '.'
                                    )
                                    ->success()
                                    ->send();
                            }
                        )
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('enrolled_at', 'desc');
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

        if ($user && $user->role === 'instructor') {
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
                                    ->whereNull('lead_instructor_id')
                                    ->whereDoesntHave('followUpInstructors');
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

        return app(FollowUpInstructorAssignmentService::class)
            ->eligibleInstructors($lesson)
            ->sortBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function eligibleInstructorOptionsForEnrollments(
        Collection $enrollments
    ): array {
        $lessonIds = $enrollments
            ->pluck('lesson_id')
            ->filter()
            ->unique()
            ->values();

        if ($lessonIds->isEmpty()) {
            return [];
        }

        $service = app(FollowUpInstructorAssignmentService::class);
        $eligibleIds = null;
        $instructorsById = collect();

        foreach ($lessonIds as $lessonId) {
            $lesson = Lesson::query()->find($lessonId);

            if (! $lesson) {
                return [];
            }

            $eligible = $service->eligibleInstructors($lesson);
            $ids = $eligible->pluck('id');

            $eligibleIds = $eligibleIds === null
                ? $ids
                : $eligibleIds->intersect($ids)->values();

            $instructorsById = $instructorsById->merge(
                $eligible->keyBy('id')
            );
        }

        if (! $eligibleIds || $eligibleIds->isEmpty()) {
            return [];
        }

        return $eligibleIds
            ->mapWithKeys(function ($id) use ($instructorsById): array {
                $instructor = $instructorsById->get($id);

                return $instructor
                    ? [$id => $instructor->name]
                    : [];
            })
            ->sort()
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
