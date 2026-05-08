<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonTopicResource\Pages;
use App\Models\LessonTopic;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class LessonTopicResource extends Resource
{
    protected static ?string $model = LessonTopic::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Lesson Topics';

    protected static ?string $navigationGroup = 'Lesson Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Lesson Topic';

    protected static ?string $pluralModelLabel = 'Lesson Topics';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Topic Information')
                    ->description('Create and manage lesson topics. Instructors can only use modules from their assigned lessons.')
                    ->schema([

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
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Topic Title')
                            ->placeholder('Example: Biblia ni Neno la Mungu')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->placeholder('biblia-ni-neno-la-mungu')
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                table: 'lesson_topics',
                                column: 'slug',
                                ignoreRecord: true
                            )
                            ->helperText('Generated from title. Used in clean URLs.'),

                        Forms\Components\TextInput::make('video_url')
                            ->label('Video URL')
                            ->url()
                            ->placeholder('https://youtu.be/...')
                            ->maxLength(255)
                            ->helperText('Optional YouTube/video link.'),

                        Forms\Components\FileUpload::make('pdf')
                            ->label('PDF Material')
                            ->disk('public')
                            ->directory('lessons/topics/pdfs')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->openable()
                            ->preserveFilenames()
                            ->helperText('Optional PDF material. Max: 5MB.'),

                        Forms\Components\RichEditor::make('content')
                            ->label('Content')
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
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('order')
                            ->label('Order')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Forms\Components\Toggle::make('is_free')
                            ->label('Free Preview')
                            ->default(false)
                            ->onColor('success'),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->onColor('success'),

                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([

                Tables\Columns\TextColumn::make('module.lesson.title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('module.title')
                    ->label('Module')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Topic Title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\IconColumn::make('pdf')
                    ->label('PDF')
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => filled($record->pdf)),

                Tables\Columns\IconColumn::make('is_free')
                    ->label('Free')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),

                Tables\Filters\TernaryFilter::make('is_free')
                    ->label('Free Preview'),

            ])
            ->actions([

                Tables\Actions\EditAction::make(),

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
            ->with('module.lesson');

        if (auth()->user()?->role === 'instructor') {
            return $query->whereHas('module.lesson', function (Builder $lessonQuery) {
                $lessonQuery->where('instructor_id', auth()->id());
            });
        }

        return $query;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessonTopics::route('/'),
            'create' => Pages\CreateLessonTopic::route('/create'),
            'edit' => Pages\EditLessonTopic::route('/{record}/edit'),
        ];
    }
}