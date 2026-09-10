<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResultResource\Pages;
use App\Models\QuizResult;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QuizResultResource extends Resource
{
    protected static ?string $model = QuizResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Lesson Quiz Results';

    protected static ?string $navigationGroup = 'Lesson Management';

    protected static ?int $navigationSort = 20;

    /*
    |--------------------------------------------------------------------------
    | Access
    |--------------------------------------------------------------------------
    | Admin sees all results.
    | Lead instructor sees all results from their lessons.
    | Follow-up instructor sees only results from students assigned to them.
    | Legacy instructor_id remains as fallback for old unconfigured lessons.
    */
    public static function shouldRegisterNavigation(): bool
    {
        return in_array(
            auth()->user()?->role,
            ['admin', 'instructor'],
            true
        );
    }

    public static function canViewAny(): bool
    {
        return in_array(
            auth()->user()?->role,
            ['admin', 'instructor'],
            true
        );
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->paginated([5, 10, 25, 50])
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quiz.title')
                    ->label('Quiz')
                    ->placeholder('No quiz')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('user_name')
                    ->label('Student')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->suffix('%')
                    ->sortable()
                    ->badge()
                    ->color(
                        fn ($state) =>
                            (int) $state >= 70
                                ? 'success'
                                : 'danger'
                    ),

                Tables\Columns\TextColumn::make('correct')
                    ->label('Correct')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->sortable(),

                Tables\Columns\IconColumn::make('passed')
                    ->label('Passed')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('passed')
                    ->label('Passed'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'admin'
                    )
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(
                            fn (): bool =>
                                auth()->user()?->role === 'admin'
                        ),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'quiz.lesson.leadInstructor',
                'quiz.lesson.followUpInstructors',
                'quiz.module.lesson.leadInstructor',
                'quiz.module.lesson.followUpInstructors',
                'quiz.topic.module.lesson.leadInstructor',
                'quiz.topic.module.lesson.followUpInstructors',
            ]);

        $user = auth()->user();

        if ($user?->role === 'instructor') {
            return static::applyInstructorAccessScope(
                $query,
                $user->id
            );
        }

        return $query;
    }

    protected static function applyInstructorAccessScope(
        Builder $query,
        int $instructorId
    ): Builder {
        return $query->where(
            function (Builder $resultQuery) use ($instructorId) {
                $resultQuery
                    /*
                    |--------------------------------------------------------------------------
                    | Lead instructor / legacy instructor
                    |--------------------------------------------------------------------------
                    */
                    ->whereHas(
                        'quiz.lesson',
                        fn (Builder $lessonQuery) =>
                            static::applyLeadOrLegacyLessonScope(
                                $lessonQuery,
                                $instructorId
                            )
                    )
                    ->orWhereHas(
                        'quiz.module.lesson',
                        fn (Builder $lessonQuery) =>
                            static::applyLeadOrLegacyLessonScope(
                                $lessonQuery,
                                $instructorId
                            )
                    )
                    ->orWhereHas(
                        'quiz.topic.module.lesson',
                        fn (Builder $lessonQuery) =>
                            static::applyLeadOrLegacyLessonScope(
                                $lessonQuery,
                                $instructorId
                            )
                    )

                    /*
                    |--------------------------------------------------------------------------
                    | Assigned follow-up instructor
                    |--------------------------------------------------------------------------
                    |
                    | Match both student and owning lesson.
                    |
                    */
                    ->orWhereExists(
                        function ($enrollmentQuery) use ($instructorId) {
                            $enrollmentQuery
                                ->selectRaw('1')
                                ->from('lesson_enrollments')
                                ->join(
                                    'quizzes',
                                    'quizzes.id',
                                    '=',
                                    'quiz_results.quiz_id'
                                )
                                ->leftJoin(
                                    'modules',
                                    'modules.id',
                                    '=',
                                    'quizzes.module_id'
                                )
                                ->leftJoin(
                                    'lesson_topics',
                                    'lesson_topics.id',
                                    '=',
                                    'quizzes.lesson_topic_id'
                                )
                                ->leftJoin(
                                    'modules as topic_modules',
                                    'topic_modules.id',
                                    '=',
                                    'lesson_topics.module_id'
                                )
                                ->whereColumn(
                                    'lesson_enrollments.user_id',
                                    'quiz_results.user_id'
                                )
                                ->where(
                                    'lesson_enrollments.follow_up_instructor_id',
                                    $instructorId
                                )
                                ->where(
                                    function ($lessonMatchQuery) {
                                        $lessonMatchQuery
                                            ->whereColumn(
                                                'lesson_enrollments.lesson_id',
                                                'quizzes.lesson_id'
                                            )
                                            ->orWhereColumn(
                                                'lesson_enrollments.lesson_id',
                                                'modules.lesson_id'
                                            )
                                            ->orWhereColumn(
                                                'lesson_enrollments.lesson_id',
                                                'topic_modules.lesson_id'
                                            );
                                    }
                                );
                        }
                    );
            }
        );
    }

    protected static function applyLeadOrLegacyLessonScope(
        Builder $query,
        int $instructorId
    ): Builder {
        return $query->where(
            function (Builder $lessonQuery) use ($instructorId) {
                $lessonQuery
                    ->where(
                        'lead_instructor_id',
                        $instructorId
                    )
                    ->orWhere(
                        function (Builder $legacyQuery) use ($instructorId) {
                            $legacyQuery
                                ->where(
                                    'instructor_id',
                                    $instructorId
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizResults::route('/'),
        ];
    }
}
