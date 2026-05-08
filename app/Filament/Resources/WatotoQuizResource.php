<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WatotoQuizResource\Pages;
use App\Models\WatotoQuiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WatotoQuizResource extends Resource
{
    protected static ?string $model = WatotoQuiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Watoto';

    protected static ?string $navigationLabel = 'Watoto Quizzes';

    protected static ?string $modelLabel = 'Watoto Quiz';

    protected static ?string $pluralModelLabel = 'Watoto Quizzes';

    /*
    |--------------------------------------------------------------------------
    | Admin Only
    |--------------------------------------------------------------------------
    | Hide Watoto Quizzes from instructors.
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

    public static function canEdit($record): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Lesson / Video')
                    ->schema([
                        Forms\Components\Select::make('watoto_video_id')
                            ->label('Select Lesson')
                            ->relationship('video', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Question')
                    ->schema([
                        Forms\Components\Textarea::make('question')
                            ->label('Question')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->label('Question Type')
                            ->options([
                                'mcq' => 'Multiple Choice',
                                'true_false' => 'True / False',
                            ])
                            ->default('mcq')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state === 'true_false') {
                                    $set('option_a', 'Kweli');
                                    $set('option_b', 'Si kweli');
                                    $set('option_c', null);
                                    $set('option_d', null);
                                    $set('correct_answer', null);
                                }

                                if ($state === 'mcq') {
                                    $set('option_a', null);
                                    $set('option_b', null);
                                    $set('option_c', null);
                                    $set('option_d', null);
                                    $set('correct_answer', null);
                                }
                            }),
                    ]),

                Forms\Components\Section::make('Answers')
                    ->schema([
                        Forms\Components\TextInput::make('option_a')
                            ->label(fn (Forms\Get $get) => $get('type') === 'true_false' ? 'Option A (True)' : 'Option A')
                            ->required()
                            ->disabled(fn (Forms\Get $get) => $get('type') === 'true_false')
                            ->dehydrated(),

                        Forms\Components\TextInput::make('option_b')
                            ->label(fn (Forms\Get $get) => $get('type') === 'true_false' ? 'Option B (False)' : 'Option B')
                            ->required()
                            ->disabled(fn (Forms\Get $get) => $get('type') === 'true_false')
                            ->dehydrated(),

                        Forms\Components\TextInput::make('option_c')
                            ->label('Option C')
                            ->required(fn (Forms\Get $get) => $get('type') === 'mcq')
                            ->hidden(fn (Forms\Get $get) => $get('type') === 'true_false')
                            ->dehydrated(fn (Forms\Get $get) => $get('type') === 'mcq'),

                        Forms\Components\TextInput::make('option_d')
                            ->label('Option D')
                            ->required(fn (Forms\Get $get) => $get('type') === 'mcq')
                            ->hidden(fn (Forms\Get $get) => $get('type') === 'true_false')
                            ->dehydrated(fn (Forms\Get $get) => $get('type') === 'mcq'),

                        Forms\Components\Select::make('correct_answer')
                            ->label('Correct Answer')
                            ->options(fn (Forms\Get $get) => $get('type') === 'true_false'
                                ? [
                                    'A' => 'A - Kweli',
                                    'B' => 'B - Si kweli',
                                ]
                                : [
                                    'A' => 'A',
                                    'B' => 'B',
                                    'C' => 'C',
                                    'D' => 'D',
                                ])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Teaching Explanation')
                    ->schema([
                        Forms\Components\Textarea::make('explanation')
                            ->label('Explanation')
                            ->rows(3)
                            ->columnSpanFull(),

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

                Tables\Columns\TextColumn::make('video.title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('question')
                    ->label('Question')
                    ->limit(50)
                    ->searchable()
                    ->wrap()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'true_false' ? 'True / False' : 'Multiple Choice')
                    ->color(fn (?string $state): string => $state === 'true_false' ? 'success' : 'primary'),

                Tables\Columns\TextColumn::make('correct_answer')
                    ->label('Correct')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                Tables\Filters\SelectFilter::make('type')
                    ->label('Question Type')
                    ->options([
                        'mcq' => 'Multiple Choice',
                        'true_false' => 'True / False',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([

                Tables\Actions\EditAction::make(),

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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWatotoQuizzes::route('/'),
            'create' => Pages\CreateWatotoQuiz::route('/create'),
            'edit' => Pages\EditWatotoQuiz::route('/{record}/edit'),
        ];
    }
}