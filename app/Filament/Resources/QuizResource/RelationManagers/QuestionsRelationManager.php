<?php

namespace App\Filament\Resources\QuizResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Quiz Questions';

    protected static ?string $modelLabel = 'Question';

    protected static ?string $pluralModelLabel = 'Questions';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return self::canManageOwnerQuiz($ownerRecord);
    }

    protected static function canManageOwnerQuiz(Model $quiz): bool
    {
        if (auth()->user()?->role === 'admin') {
            return true;
        }

        if (auth()->user()?->role === 'instructor') {
            return $quiz->lesson?->instructor_id === auth()->id()
                || $quiz->module?->lesson?->instructor_id === auth()->id()
                || $quiz->topic?->module?->lesson?->instructor_id === auth()->id();
        }

        return false;
    }

    protected function canManageCurrentQuiz(): bool
    {
        return self::canManageOwnerQuiz($this->getOwnerRecord());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Question Details')
                    ->description('Add quiz questions and mark the correct answer.')
                    ->schema([

                        Forms\Components\Textarea::make('question')
                            ->label('Question')
                            ->placeholder('Example: Maombi ni nini?')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->label('Question Type')
                            ->options([
                                'multiple_choice' => 'Multiple Choice',
                                'true_false' => 'True / False',
                            ])
                            ->default('multiple_choice')
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Order')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->onColor('success'),

                        Forms\Components\Repeater::make('options')
                            ->label('Answer Options')
                            ->schema([
                                Forms\Components\TextInput::make('text')
                                    ->label('Option Text')
                                    ->placeholder('Example: Mazungumzo na Mungu')
                                    ->required(),

                                Forms\Components\Toggle::make('is_correct')
                                    ->label('Correct Answer')
                                    ->default(false)
                                    ->onColor('success'),
                            ])
                            ->defaultItems(4)
                            ->minItems(2)
                            ->maxItems(6)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['text'] ?? 'Option')
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => $get('type') === 'multiple_choice')
                            ->required(fn (Get $get) => $get('type') === 'multiple_choice')
                            ->helperText('Important: mark only one option as the correct answer.'),

                        Forms\Components\Select::make('correct_answer')
                            ->label('Correct Answer')
                            ->options([
                                'true' => 'True',
                                'false' => 'False',
                            ])
                            ->visible(fn (Get $get) => $get('type') === 'true_false')
                            ->required(fn (Get $get) => $get('type') === 'true_false'),

                        Forms\Components\Textarea::make('explanation')
                            ->label('Explanation')
                            ->placeholder('Optional explanation shown after answering.')
                            ->rows(3)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('question')
                    ->label('Question')
                    ->searchable()
                    ->wrap()
                    ->limit(90)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True / False',
                        default => 'Unknown',
                    }),

                Tables\Columns\TextColumn::make('options_count')
                    ->label('Options')
                    ->getStateUsing(function ($record) {
                        if (! is_array($record->options)) {
                            return $record->type === 'true_false' ? '2' : '0';
                        }

                        return count($record->options);
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->defaultSort('sort_order')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Question')
                    ->visible(fn (): bool => $this->canManageCurrentQuiz())
                    ->mutateFormDataUsing(fn (array $data): array => self::prepareQuestionData($data)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit Question')
                    ->visible(fn (): bool => $this->canManageCurrentQuiz())
                    ->mutateFormDataUsing(fn (array $data): array => self::prepareQuestionData($data)),

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

    protected static function prepareQuestionData(array $data): array
    {
        if (($data['type'] ?? null) === 'multiple_choice') {
            $options = $data['options'] ?? [];

            $correctOptions = collect($options)
                ->filter(fn ($option) => ! empty($option['is_correct']))
                ->count();

            if ($correctOptions !== 1) {
                throw ValidationException::withMessages([
                    'options' => 'For multiple choice questions, you must mark exactly one option as correct.',
                ]);
            }

            $data['correct_answer'] = null;
        }

        if (($data['type'] ?? null) === 'true_false') {
            $data['options'] = null;
        }

        return $data;
    }
}