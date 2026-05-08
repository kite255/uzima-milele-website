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

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('question')
                    ->label('Question')
                    ->searchable()
                    ->limit(55)
                    ->wrap(),

                Tables\Columns\TextColumn::make('answer')
                    ->label('Answer Status')
                    ->formatStateUsing(fn ($state) => filled($state) ? 'Answered' : 'Pending')
                    ->badge()
                    ->color(fn ($state) => filled($state) ? 'success' : 'warning'),

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

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),

                Tables\Filters\Filter::make('answered')
                    ->label('Answered')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('answer')),

                Tables\Filters\Filter::make('unanswered')
                    ->label('Unanswered')
                    ->query(fn (Builder $query): Builder => $query->whereNull('answer')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Answer/Edit')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (filled($data['answer'] ?? null)) {
                            $data['answered_by'] = auth()->id();
                            $data['answered_at'] = $data['answered_at'] ?? now();
                        }

                        return $data;
                    })
                    ->after(function (LessonQuestion $record): void {
                        static::notifyStudentIfQuestionAnswered($record);
                    }),

                Tables\Actions\Action::make('markAnswered')
                    ->label('Mark Answered')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LessonQuestion $record) => blank($record->answered_at) && filled($record->answer))
                    ->action(function (LessonQuestion $record): void {
                        $record->update([
                            'answered_by' => auth()->id(),
                            'answered_at' => now(),
                        ]);

                        static::notifyStudentIfQuestionAnswered($record);
                    }),

                Tables\Actions\DeleteAction::make(),
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
        $query = parent::getEloquentQuery()
            ->with(['lesson', 'user', 'answeredBy']);

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

        /*
        |--------------------------------------------------------------------------
        | Avoid sending notification on every small edit
        |--------------------------------------------------------------------------
        | Notify mainly when answer or answered_at has just changed.
        */
        if (! $record->wasChanged('answer') && ! $record->wasChanged('answered_at')) {
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

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (filled($data['answer'] ?? null)) {
            $data['answered_by'] = auth()->id();
            $data['answered_at'] = $data['answered_at'] ?? now();
        }

        return $data;
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