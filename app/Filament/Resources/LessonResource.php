<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonResource\Pages;
use App\Filament\Resources\LessonResource\RelationManagers\ModulesRelationManager;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification as AdminNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Lesson Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Lesson';

    protected static ?string $pluralModelLabel = 'Lessons';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Course Information')
                    ->description('Basic information shown on the public lesson page.')
                    ->schema([

                        Forms\Components\TextInput::make('title')
                            ->label('Course Title')
                            ->placeholder('Example: Nguvu ya Maombi Katika Maisha ya Mkristo')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('URL Slug')
                            ->placeholder('nguvu-ya-maombi-katika-maisha-ya-mkristo')
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                table: 'lessons',
                                column: 'slug',
                                ignoreRecord: true
                            )
                            ->helperText('Example public URL: /lessons/nguvu-ya-maombi-katika-maisha-ya-mkristo'),

                        Forms\Components\Select::make('instructor_id')
                            ->label('Instructor')
                            ->relationship(
                                name: 'instructor',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->whereIn('role', ['admin', 'instructor'])
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Uzima Milele Ministry')
                            ->helperText('Select instructor or leave empty to show Uzima Milele Ministry.'),

                        Forms\Components\Textarea::make('description')
                            ->label('Short Description')
                            ->placeholder('Write a short summary of what the student will learn.')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('This appears on lesson cards and the lesson landing page.'),

                        Forms\Components\FileUpload::make('cover_image')
                            ->label('Cover Image')
                            ->image()
                            ->disk('public')
                            ->directory('lessons/covers')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('12:7')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('700')
                            ->maxSize(2048)
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->downloadable()
                            ->openable()
                            ->helperText('Recommended: 1200 x 700 px. Max: 2MB. Use optimized JPG/WebP.'),

                        Forms\Components\TextInput::make('video_url')
                            ->label('Intro Video URL')
                            ->url()
                            ->placeholder('https://youtu.be/...')
                            ->helperText('Optional. Add a YouTube intro video link if available.'),

                        Forms\Components\FileUpload::make('pdf')
                            ->label('Downloadable PDF Material')
                            ->disk('public')
                            ->directory('lessons/pdfs')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->preserveFilenames()
                            ->helperText('Optional course notes/workbook. Max: 5MB.'),

                        Forms\Components\Select::make('category')
                            ->label('Category')
                            ->options([
                                'Bible Study' => 'Bible Study',
                                'Christian Life' => 'Christian Life',
                                'Prayer' => 'Prayer',
                                'Family' => 'Family',
                                'Health' => 'Health',
                                'Children Ministry' => 'Children Ministry',
                                'Youth' => 'Youth',
                                'Prophecy' => 'Prophecy',
                                'Discipleship' => 'Discipleship',
                            ])
                            ->searchable()
                            ->placeholder('Select category'),

                        Forms\Components\Select::make('level')
                            ->label('Level')
                            ->options([
                                'Beginner' => 'Beginner',
                                'Intermediate' => 'Intermediate',
                                'Advanced' => 'Advanced',
                            ])
                            ->searchable()
                            ->placeholder('Select level'),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(false)
                            ->onColor('success')
                            ->helperText('Turn this on when the lesson is ready to appear on the public website.'),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Course Timeline & Schedule Settings')
                    ->description('The course owner/admin sets the allocated course time. Students choose their study pace, and the system calculates the target completion date.')
                    ->schema([

                        Forms\Components\TextInput::make('estimated_duration_minutes')
                            ->label('Total Course Time')
                            ->numeric()
                            ->minValue(1)
                            ->default(180)
                            ->required()
                            ->suffix('minutes')
                            ->live(onBlur: true)
                            ->placeholder('Example: 120')
                            ->helperText('Total time allocated by the course owner. Example: 60 = 1 hour, 120 = 2 hours, 180 = 3 hours.'),

                        Forms\Components\Placeholder::make('duration_preview')
                            ->label('Duration Preview')
                            ->content(function (Forms\Get $get): string {
                                $minutesTotal = (int) ($get('estimated_duration_minutes') ?: 180);

                                $hours = intdiv($minutesTotal, 60);
                                $minutes = $minutesTotal % 60;

                                if ($hours > 0 && $minutes > 0) {
                                    return "{$hours} hour(s) {$minutes} minute(s)";
                                }

                                if ($hours > 0) {
                                    return "{$hours} hour(s)";
                                }

                                return "{$minutes} minute(s)";
                            }),

                        Forms\Components\Select::make('recommended_study_pace')
                            ->label('Recommended Study Pace')
                            ->options([
                                Lesson::PACE_RELAXED => 'Taratibu - 1 hour/week',
                                Lesson::PACE_REGULAR => 'Kawaida - 3 hours/week',
                                Lesson::PACE_INTENSIVE => 'Haraka - 5 hours/week',
                                Lesson::PACE_CUSTOM => 'Ratiba Maalum - Student chooses hours/week',
                            ])
                            ->default(Lesson::PACE_REGULAR)
                            ->searchable()
                            ->required()
                            ->helperText('This is the recommended/default learning pace for students.'),

                        Forms\Components\TextInput::make('min_completion_days')
                            ->label('Minimum Completion Days')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Example: 3')
                            ->rules([
                                fn (Forms\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $maxDays = (int) $get('max_completion_days');

                                    if ($value && $maxDays && (int) $value > $maxDays) {
                                        $fail('Minimum completion days cannot be greater than maximum completion days.');
                                    }
                                },
                            ])
                            ->helperText('Optional. Prevents unrealistic short completion time.'),

                        Forms\Components\TextInput::make('max_completion_days')
                            ->label('Maximum Completion Days')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Example: 30')
                            ->rules([
                                fn (Forms\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $minDays = (int) $get('min_completion_days');

                                    if ($value && $minDays && (int) $value < $minDays) {
                                        $fail('Maximum completion days cannot be less than minimum completion days.');
                                    }
                                },
                            ])
                            ->helperText('Optional. Maximum recommended days to complete this course.'),

                        Forms\Components\DatePicker::make('course_deadline')
                            ->label('Course Deadline')
                            ->native(false)
                            ->placeholder('Select deadline date')
                            ->helperText('Optional. Student target completion date should not go beyond this date.'),

                        Forms\Components\Toggle::make('allow_schedule_reset')
                            ->label('Allow Student to Reset Schedule')
                            ->default(true)
                            ->onColor('success')
                            ->helperText('If enabled, students may later change their learning schedule.'),

                        Forms\Components\TextInput::make('reminder_days_before_deadline')
                            ->label('Reminder Days Before Deadline')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('Example: 2')
                            ->helperText('Optional. Used later for reminders before student target/deadline.'),

                        Forms\Components\Placeholder::make('schedule_example')
                            ->label('Example')
                            ->content(function (Forms\Get $get): string {
                                $minutesTotal = (int) ($get('estimated_duration_minutes') ?: 120);
                                $estimatedHours = max(1, (int) ceil($minutesTotal / 60));

                                if ($estimatedHours <= 3) {
                                    return 'For a short lesson like 2 hours: Taratibu ≈ 2 weeks, Kawaida ≈ 1 week, Haraka ≈ 3 days.';
                                }

                                return "For this {$estimatedHours}-hour lesson, the system will calculate target dates based on student pace.";
                            })
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->collapsible(),

                Forms\Components\Section::make('Course Overview')
                    ->description('Write the lesson overview, objectives, and introduction here.')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Overview Content')
                            ->placeholder('Write the course overview, learning objectives, main scripture, and course structure here.')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'link',
                                'undo',
                                'redo',
                            ])
                            ->columnSpanFull()
                            ->helperText('Do not put all module/topic content here. Use this area for course overview only.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Course Title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('instructor.name')
                    ->label('Instructor')
                    ->placeholder('Uzima Milele Ministry')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->placeholder('No category')
                    ->searchable(),

                Tables\Columns\TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->placeholder('No level')
                    ->searchable(),

                Tables\Columns\TextColumn::make('estimated_duration_label')
                    ->label('Owner Time')
                    ->placeholder('—')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('estimated_duration_minutes', $direction);
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('recommended_study_pace')
                    ->label('Recommended Pace')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        Lesson::PACE_RELAXED => 'Taratibu',
                        Lesson::PACE_REGULAR => 'Kawaida',
                        Lesson::PACE_INTENSIVE => 'Haraka',
                        Lesson::PACE_CUSTOM => 'Ratiba Maalum',
                        default => '—',
                    })
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('default_completion_label')
                    ->label('Estimated Completion')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('min_completion_days')
                    ->label('Min Days')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('max_completion_days')
                    ->label('Max Days')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('course_deadline')
                    ->label('Deadline')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('allow_schedule_reset')
                    ->label('Reset')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\TextColumn::make('modules_count')
                    ->label('Modules')
                    ->counts('modules')
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrollments_count')
                    ->label('Students')
                    ->counts('enrollments')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M, Y')
                    ->sortable(),

            ])
            ->filters([

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),

                Tables\Filters\SelectFilter::make('instructor_id')
                    ->label('Instructor')
                    ->relationship(
                        name: 'instructor',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereIn('role', ['admin', 'instructor'])
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'Bible Study' => 'Bible Study',
                        'Christian Life' => 'Christian Life',
                        'Prayer' => 'Prayer',
                        'Family' => 'Family',
                        'Health' => 'Health',
                        'Children Ministry' => 'Children Ministry',
                        'Youth' => 'Youth',
                        'Prophecy' => 'Prophecy',
                        'Discipleship' => 'Discipleship',
                    ]),

                Tables\Filters\SelectFilter::make('level')
                    ->label('Level')
                    ->options([
                        'Beginner' => 'Beginner',
                        'Intermediate' => 'Intermediate',
                        'Advanced' => 'Advanced',
                    ]),

                Tables\Filters\SelectFilter::make('recommended_study_pace')
                    ->label('Recommended Pace')
                    ->options([
                        Lesson::PACE_RELAXED => 'Taratibu',
                        Lesson::PACE_REGULAR => 'Kawaida',
                        Lesson::PACE_INTENSIVE => 'Haraka',
                        Lesson::PACE_CUSTOM => 'Ratiba Maalum',
                    ]),
            ])
            ->actions([

                Tables\Actions\Action::make('viewPublic')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Lesson $record): string => route('lessons.show', $record->slug))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('sendReminder')
                    ->label('Send Reminder')
                    ->icon('heroicon-o-bell-alert')
                    ->color('warning')
                    ->modalHeading('Send Lesson Reminder')
                    ->modalDescription('This reminder checks students who enrolled but have not completed all topics. You can later connect this action to email, SMS, or notifications.')
                    ->requiresConfirmation()
                    ->action(function (Lesson $record): void {
                        $totalTopics = $record->modules()
                            ->where('is_published', true)
                            ->withCount([
                                'topics' => fn (Builder $query) => $query->where('is_published', true),
                            ])
                            ->get()
                            ->sum('topics_count');

                        if ($totalTopics <= 0) {
                            AdminNotification::make()
                                ->title('No published topics found')
                                ->body('This lesson has no published topics to track.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $pendingStudents = 0;

                        $enrollments = $record->enrollments()
                            ->with('user')
                            ->get();

                        foreach ($enrollments as $enrollment) {
                            if (! $enrollment->user) {
                                continue;
                            }

                            $completedTopics = LessonProgress::query()
                                ->where('user_id', $enrollment->user_id)
                                ->where('lesson_id', $record->id)
                                ->distinct('lesson_topic_id')
                                ->count('lesson_topic_id');

                            if ($completedTopics < $totalTopics) {
                                $pendingStudents++;
                            }
                        }

                        AdminNotification::make()
                            ->title('Reminder check completed')
                            ->body("Pending students: {$pendingStudents}. You can connect email/SMS sending later.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->label('Manage Course'),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->role === 'instructor') {
            return $query->where('instructor_id', auth()->id());
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            ModulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessons::route('/'),
            'create' => Pages\CreateLesson::route('/create'),
            'edit' => Pages\EditLesson::route('/{record}/edit'),
        ];
    }
}