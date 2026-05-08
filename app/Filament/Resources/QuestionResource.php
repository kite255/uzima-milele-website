<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'Quiz Questions';

    protected static ?string $navigationGroup = 'Lessons Management';

    protected static ?int $navigationSort = 4;

    /*
    |--------------------------------------------------------------------------
    | Admin Only
    |--------------------------------------------------------------------------
    | This global questions page is hidden from instructors.
    | Instructors must manage questions only through:
    | Quizzes → Manage Questions
    */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Question Details')
                ->schema([
                    Forms\Components\Select::make('quiz_id')
                        ->label('Quiz')
                        ->relationship('quiz', 'title')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Select the quiz this question belongs to.'),

                    Forms\Components\TextInput::make('question')
                        ->label('Question')
                        ->required()
                        ->maxLength(255),

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
                        ->label('Question Order')
                        ->numeric()
                        ->default(1)
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Answer Options')
                ->schema([
                    Forms\Components\Repeater::make('options')
                        ->label('Multiple Choice Options')
                        ->schema([
                            Forms\Components\TextInput::make('text')
                                ->label('Option Text')
                                ->required(),

                            Forms\Components\Toggle::make('is_correct')
                                ->label('Correct Answer')
                                ->live()
                                ->onColor('success')
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    if (! $state) {
                                        return;
                                    }

                                    $options = $get('../../options') ?? [];

                                    foreach ($options as $key => $option) {
                                        $set("../../options.$key.is_correct", false);
                                    }

                                    $set('is_correct', true);
                                }),
                        ])
                        ->minItems(2)
                        ->maxItems(6)
                        ->defaultItems(4)
                        ->columns(2)
                        ->visible(fn (Forms\Get $get) => $get('type') === 'multiple_choice'),

                    Forms\Components\Select::make('correct_answer')
                        ->label('Correct Answer')
                        ->options([
                            'true' => 'True',
                            'false' => 'False',
                        ])
                        ->required(fn (Forms\Get $get) => $get('type') === 'true_false')
                        ->visible(fn (Forms\Get $get) => $get('type') === 'true_false'),
                ]),

            Forms\Components\Section::make('Extra Settings')
                ->schema([
                    Forms\Components\Textarea::make('explanation')
                        ->label('Answer Explanation')
                        ->rows(4),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->onColor('success'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quiz.title')
                    ->label('Quiz')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('question')
                    ->label('Question')
                    ->searchable()
                    ->limit(60)
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True / False',
                        default => 'Unknown',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('quiz_id')
                    ->label('Quiz')
                    ->relationship('quiz', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'multiple_choice' => 'Multiple Choice',
                        'true_false' => 'True / False',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function validateQuestionOptions(array $data): void
    {
        if (($data['type'] ?? null) === 'multiple_choice') {
            $options = $data['options'] ?? [];

            if (count($options) < 2) {
                Notification::make()
                    ->title('At least 2 options are required.')
                    ->danger()
                    ->send();

                throw ValidationException::withMessages([
                    'options' => 'At least 2 options are required.',
                ]);
            }

            $correctAnswers = collect($options)
                ->where('is_correct', true)
                ->count();

            if ($correctAnswers !== 1) {
                Notification::make()
                    ->title('Select only one correct answer.')
                    ->body('Multiple choice questions must have exactly one correct option.')
                    ->danger()
                    ->send();

                throw ValidationException::withMessages([
                    'options' => 'Multiple choice questions must have exactly one correct answer.',
                ]);
            }
        }

        if (($data['type'] ?? null) === 'true_false' && blank($data['correct_answer'] ?? null)) {
            Notification::make()
                ->title('Choose True or False.')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'correct_answer' => 'Correct answer is required.',
            ]);
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}