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
    | Instructor sees only results from lessons assigned to them.
    */
    public static function shouldRegisterNavigation(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'instructor']);
    }

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'instructor']);
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
                    ->color(fn ($state) => (int) $state >= 70 ? 'success' : 'danger'),

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
                    ->visible(fn (): bool => auth()->user()?->role === 'admin')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->role === 'admin'),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'quiz.lesson',
                'quiz.module.lesson',
                'quiz.topic.module.lesson',
            ]);

        if (auth()->user()?->role === 'instructor') {
            return $query->whereHas('quiz', function (Builder $quizQuery) {
                $quizQuery
                    ->whereHas('lesson', function (Builder $lessonQuery) {
                        $lessonQuery->where('instructor_id', auth()->id());
                    })
                    ->orWhereHas('module.lesson', function (Builder $lessonQuery) {
                        $lessonQuery->where('instructor_id', auth()->id());
                    })
                    ->orWhereHas('topic.module.lesson', function (Builder $lessonQuery) {
                        $lessonQuery->where('instructor_id', auth()->id());
                    });
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizResults::route('/'),
        ];
    }
}