<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonQuestionResource\Pages;
use App\Models\LessonQuestion;
use App\Notifications\LessonQuestionAnsweredNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LessonQuestionResource extends Resource
{
    protected static ?string $model = LessonQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Lesson Management';

    protected static ?string $navigationLabel = 'Lesson Q&A';

    protected static ?string $modelLabel = 'Lesson Question';

    protected static ?string $pluralModelLabel = 'Lesson Questions';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Question Details')
                    ->schema([
                        Forms\Components\Select::make('lesson_id')
                            ->label('Lesson')
                            ->relationship('lesson', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabledOn('edit'),

                        Forms\Components\Select::make('lesson_topic_id')
                            ->label('Topic')
                            ->relationship('lessonTopic', 'title')
                            ->searchable()
                            ->preload()
                            ->disabledOn('edit'),

                        Forms\Components\Select::make('user_id')
                            ->label('Student')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabledOn('edit'),

                        Forms\Components\Textarea::make('question')
                            ->label('Student Question')
                            ->required()
                            ->rows(5)
                            ->disabledOn('edit')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Instructor/Admin Answer')
                    ->schema([
                        Forms\Components\Textarea::make('answer')
                            ->label('Answer')
                            ->rows(6)
                            ->placeholder('Write the answer here...')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                LessonQuestion::STATUS_PENDING => 'Pending',
                                LessonQuestion::STATUS_ANSWERED => 'Answered',
                                LessonQuestion::STATUS_CLOSED => 'Closed',
                            ])
                            ->default(LessonQuestion::STATUS_PENDING)
                            ->required(),

                        Forms\Components\Select::make('visibility')
                            ->label('Visibility')
                            ->options([
                                LessonQuestion::VISIBILITY_PRIVATE => 'Private - only student and admin/instructor',
                                LessonQuestion::VISIBILITY_PUBLIC => 'Public - visible to other students',
                                LessonQuestion::VISIBILITY_HIDDEN => 'Hidden - hide from student Q&A',
                            ])
                            ->default(LessonQuestion::VISIBILITY_PRIVATE)
                            ->required(),

                        Forms\Components\Hidden::make('answered_by')
                            ->default(fn () => auth()->id()),

                        Forms\Components\DateTimePicker::make('answered_at')
                            ->label('Answered At')
                            ->seconds(false)
                            ->default(fn (?LessonQuestion $record) => $record?->answered_at),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable()
                    ->limit(35),

                Tables\Columns\TextColumn::make('lessonTopic.title')
                    ->label('Topic')
                    ->placeholder('General Question')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('question')
                    ->label('Question')
                    ->searchable()
                    ->limit(55)
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        LessonQuestion::STATUS_ANSWERED => 'Answered',
                        LessonQuestion::STATUS_CLOSED => 'Closed',
                        default => 'Pending',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        LessonQuestion::STATUS_ANSWERED => 'success',
                        LessonQuestion::STATUS_CLOSED => 'gray',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('visibility')
                    ->label('Visibility')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        LessonQuestion::VISIBILITY_PUBLIC => 'Public',
                        LessonQuestion::VISIBILITY_HIDDEN => 'Hidden',
                        default => 'Private',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        LessonQuestion::VISIBILITY_PUBLIC => 'info',
                        LessonQuestion::VISIBILITY_HIDDEN => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('answer')
                    ->label('Answer')
                    ->formatStateUsing(fn ($state) => filled($state) ? 'Has answer' : 'No answer')
                    ->badge()
                    ->color(fn ($state) => filled($state) ? 'success' : 'warning')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('answeredBy.name')
                    ->label('Answered By')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('answered_at')
                    ->label('Answered At')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Asked At')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lesson_id')
                    ->label('Lesson')
                    ->relationship('lesson', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        LessonQuestion::STATUS_PENDING => 'Pending',
                        LessonQuestion::STATUS_ANSWERED => 'Answered',
                        LessonQuestion::STATUS_CLOSED => 'Closed',
                    ]),

                Tables\Filters\SelectFilter::make('visibility')
                    ->label('Visibility')
                    ->options([
                        LessonQuestion::VISIBILITY_PRIVATE => 'Private',
                        LessonQuestion::VISIBILITY_PUBLIC => 'Public',
                        LessonQuestion::VISIBILITY_HIDDEN => 'Hidden',
                    ]),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),

                Tables\Filters\Filter::make('answered')
                    ->label('Answered')
                    ->query(fn (Builder $query): Builder => $query
                        ->where(function (Builder $query) {
                            $query->whereNotNull('answer')
                                ->orWhere('status', LessonQuestion::STATUS_ANSWERED);
                        })),

                Tables\Filters\Filter::make('unanswered')
                    ->label('Unanswered')
                    ->query(fn (Builder $query): Builder => $query
                        ->where(function (Builder $query) {
                            $query->whereNull('answer')
                                ->orWhere('status', LessonQuestion::STATUS_PENDING);
                        })),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Answer/Edit')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (filled($data['answer'] ?? null)) {
                            $data['status'] = LessonQuestion::STATUS_ANSWERED;
                            $data['answered_by'] = auth()->id();
                            $data['answered_at'] = $data['answered_at'] ?? now();
                        }

                        $data['visibility'] = $data['visibility'] ?? LessonQuestion::VISIBILITY_PRIVATE;

                        return $data;
                    })
                    ->after(function (LessonQuestion $record): void {
                        static::notifyStudentIfQuestionAnswered($record);
                    }),

                Tables\Actions\Action::make('markAnswered')
                    ->label('Mark Answered')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LessonQuestion $record): bool => filled($record->answer) && $record->status !== LessonQuestion::STATUS_ANSWERED)
                    ->action(function (LessonQuestion $record): void {
                        $record->update([
                            'status' => LessonQuestion::STATUS_ANSWERED,
                            'answered_by' => auth()->id(),
                            'answered_at' => $record->answered_at ?? now(),
                            'visibility' => $record->visibility ?? LessonQuestion::VISIBILITY_PRIVATE,
                        ]);

                        static::notifyStudentIfQuestionAnswered($record);
                    }),

                Tables\Actions\Action::make('makePublic')
                    ->label('Make Public')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (LessonQuestion $record): bool => $record->visibility !== LessonQuestion::VISIBILITY_PUBLIC)
                    ->requiresConfirmation()
                    ->action(function (LessonQuestion $record): void {
                        $record->update([
                            'visibility' => LessonQuestion::VISIBILITY_PUBLIC,
                        ]);

                        FilamentNotification::make()
                            ->title('Question is now public')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('makePrivate')
                    ->label('Make Private')
                    ->icon('heroicon-o-lock-closed')
                    ->color('gray')
                    ->visible(fn (LessonQuestion $record): bool => $record->visibility !== LessonQuestion::VISIBILITY_PRIVATE)
                    ->requiresConfirmation()
                    ->action(function (LessonQuestion $record): void {
                        $record->update([
                            'visibility' => LessonQuestion::VISIBILITY_PRIVATE,
                        ]);

                        FilamentNotification::make()
                            ->title('Question is now private')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('hideQuestion')
                    ->label('Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('danger')
                    ->visible(fn (LessonQuestion $record): bool => $record->visibility !== LessonQuestion::VISIBILITY_HIDDEN)
                    ->requiresConfirmation()
                    ->action(function (LessonQuestion $record): void {
                        $record->update([
                            'visibility' => LessonQuestion::VISIBILITY_HIDDEN,
                        ]);

                        FilamentNotification::make()
                            ->title('Question hidden')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markAnswered')
                        ->label('Mark as Answered')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                if (filled($record->answer)) {
                                    $record->update([
                                        'status' => LessonQuestion::STATUS_ANSWERED,
                                        'answered_by' => auth()->id(),
                                        'answered_at' => $record->answered_at ?? now(),
                                    ]);
                                }
                            }

                            FilamentNotification::make()
                                ->title('Selected questions updated')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('makePrivate')
                        ->label('Make Private')
                        ->icon('heroicon-o-lock-closed')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'visibility' => LessonQuestion::VISIBILITY_PRIVATE,
                                ]);
                            }

                            FilamentNotification::make()
                                ->title('Selected questions are now private')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('makePublic')
                        ->label('Make Public')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'visibility' => LessonQuestion::VISIBILITY_PUBLIC,
                                ]);
                            }

                            FilamentNotification::make()
                                ->title('Selected questions are now public')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['lesson', 'lessonTopic', 'user', 'answeredBy']);

        $user = auth()->user();

        if ($user && $user->role === 'instructor') {
            return $query->whereHas('lesson', function (Builder $lessonQuery) use ($user) {
                $lessonQuery->where('instructor_id', $user->id);
            });
        }

        return $query;
    }

    protected static function notifyStudentIfQuestionAnswered(LessonQuestion $record): void
    {
        $record->loadMissing(['user', 'lesson']);

        if (! $record->user) {
            return;
        }

        if (blank($record->answer)) {
            return;
        }

        if ($record->status !== LessonQuestion::STATUS_ANSWERED) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Avoid sending notification on every small edit
        |--------------------------------------------------------------------------
        | Notify mainly when answer, answered_at, or status has just changed.
        */
        if (
            ! $record->wasChanged('answer')
            && ! $record->wasChanged('answered_at')
            && ! $record->wasChanged('status')
        ) {
            return;
        }

        $record->user->notify(
            new LessonQuestionAnsweredNotification($record)
        );

        FilamentNotification::make()
            ->title('Student notified')
            ->body('The student has been notified that their question was answered.')
            ->success()
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessonQuestions::route('/'),
            'create' => Pages\CreateLessonQuestion::route('/create'),
            'edit' => Pages\EditLessonQuestion::route('/{record}/edit'),
        ];
    }
}