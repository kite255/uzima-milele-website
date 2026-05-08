<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Filament\Resources\QuizResource\RelationManagers\QuestionsRelationManager;
use App\Models\Lesson;
use App\Models\LessonTopic;
use App\Models\Module;
use App\Models\Quiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationLabel = 'Quizzes';

    protected static ?string $navigationGroup = 'Lesson Management';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Quiz';

    protected static ?string $pluralModelLabel = 'Quizzes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Quiz Target')
                    ->description('Choose where this quiz belongs: topic, module, or final lesson.')
                    ->schema([

                        Forms\Components\Select::make('quiz_scope')
                            ->label('Quiz Level')
                            ->options([
                                'topic' => 'Topic Quiz',
                                'module' => 'Module Quiz',
                                'final' => 'Final Lesson Quiz',
                            ])
                            ->default('topic')
                            ->required()
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Set $set, ?Quiz $record) {
                                if (! $record) {
                                    return;
                                }

                                if ($record->lesson_topic_id) {
                                    $set('quiz_scope', 'topic');
                                } elseif ($record->module_id) {
                                    $set('quiz_scope', 'module');
                                } elseif ($record->lesson_id) {
                                    $set('quiz_scope', 'final');
                                }
                            })
                            ->afterStateUpdated(function (Set $set) {
                                $set('lesson_topic_id', null);
                                $set('module_id', null);
                                $set('lesson_id', null);
                            }),

                        Forms\Components\Select::make('lesson_topic_id')
                            ->label('Lesson Topic')
                            ->options(function () {
                                return LessonTopic::query()
                                    ->with('module.lesson')
                                    ->when(auth()->user()?->role === 'instructor', function (Builder $query) {
                                        $query->whereHas('module.lesson', function (Builder $lessonQuery) {
                                            $lessonQuery->where('instructor_id', auth()->id());
                                        });
                                    })
                                    ->orderBy('module_id')
                                    ->orderBy('order')
                                    ->get()
                                    ->mapWithKeys(function (LessonTopic $topic) {
                                        $lessonTitle = $topic->module?->lesson?->title ?? 'No Lesson';
                                        $moduleTitle = $topic->module?->title ?? 'No Module';

                                        return [
                                            $topic->id => "{$lessonTitle} → {$moduleTitle} → {$topic->title}",
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get) => $get('quiz_scope') === 'topic')
                            ->visible(fn (Get $get) => $get('quiz_scope') === 'topic'),

                        Forms\Components\Select::make('module_id')
                            ->label('Module')
                            ->options(function () {
                                return Module::query()
                                    ->with('lesson')
                                    ->when(auth()->user()?->role === 'instructor', function (Builder $query) {
                                        $query->whereHas('lesson', function (Builder $lessonQuery) {
                                            $lessonQuery->where('instructor_id', auth()->id());
                                        });
                                    })
                                    ->orderBy('lesson_id')
                                    ->orderBy('order')
                                    ->get()
                                    ->mapWithKeys(function (Module $module) {
                                        $lessonTitle = $module->lesson?->title ?? 'No Lesson';

                                        return [
                                            $module->id => "{$lessonTitle} → {$module->title}",
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get) => $get('quiz_scope') === 'module')
                            ->visible(fn (Get $get) => $get('quiz_scope') === 'module'),

                        Forms\Components\Select::make('lesson_id')
                            ->label('Lesson')
                            ->options(function () {
                                return Lesson::query()
                                    ->when(auth()->user()?->role === 'instructor', function (Builder $query) {
                                        $query->where('instructor_id', auth()->id());
                                    })
                                    ->orderBy('title')
                                    ->pluck('title', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get) => $get('quiz_scope') === 'final')
                            ->visible(fn (Get $get) => $get('quiz_scope') === 'final'),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Quiz Settings')
                    ->description('Set title, quiz type, pass mark, and publishing status.')
                    ->schema([

                        Forms\Components\TextInput::make('title')
                            ->label('Quiz Title')
                            ->placeholder('Example: Quiz ya Maombi')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('quiz_type')
                            ->label('Quiz Type')
                            ->options([
                                'kujipima' => 'Kujipima - Practice',
                                'kupimwa' => 'Kupimwa - Assessed',
                            ])
                            ->default('kujipima')
                            ->required()
                            ->helperText('Kujipima = practice quiz. Kupimwa = graded/assessed quiz.'),

                        Forms\Components\TextInput::make('pass_mark')
                            ->label('Pass Mark (%)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->default(70)
                            ->required(),

                        Forms\Components\Toggle::make('is_required')
                            ->label('Required to Complete?')
                            ->default(false)
                            ->onColor('success')
                            ->helperText('Turn on if student must pass this quiz to complete the lesson/module.'),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->onColor('success'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Short explanation of this quiz.')
                            ->rows(3)
                            ->columnSpanFull(),

                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('title')
                    ->label('Quiz')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('quiz_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'kujipima' => 'Kujipima',
                        'kupimwa' => 'Kupimwa',
                        default => 'Not set',
                    }),

                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Final Lesson')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('module.title')
                    ->label('Module')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('topic.title')
                    ->label('Topic')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('pass_mark')
                    ->label('Pass Mark')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('Questions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([

                Tables\Filters\SelectFilter::make('quiz_type')
                    ->label('Quiz Type')
                    ->options([
                        'kujipima' => 'Kujipima',
                        'kupimwa' => 'Kupimwa',
                    ]),

                Tables\Filters\TernaryFilter::make('is_required')
                    ->label('Required'),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),

            ])
            ->actions([

                Tables\Actions\EditAction::make()
                    ->label('Manage Questions'),

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
        $query = parent::getEloquentQuery()
            ->with(['lesson', 'module.lesson', 'topic.module.lesson']);

        if (auth()->user()?->role === 'instructor') {
            return $query->where(function (Builder $query) {
                $query
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

    public static function getRelations(): array
    {
        return [
            QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}