<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuleResource\Pages;
use App\Filament\Resources\ModuleResource\RelationManagers\TopicsRelationManager;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Lesson Modules';

    protected static ?string $modelLabel = 'Lesson Module';

    protected static ?string $pluralModelLabel = 'Lesson Modules';

    protected static ?string $navigationGroup = 'Lesson Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Module Information')
                    ->description('Create modules/chapters for a lesson. Each module can contain multiple topics.')
                    ->schema([

                        Forms\Components\Select::make('lesson_id')
                            ->label('Lesson')
                            ->relationship(
                                name: 'lesson',
                                titleAttribute: 'title',
                                modifyQueryUsing: function (Builder $query) {
                                    if (auth()->user()?->role === 'instructor') {
                                        return $query->where('instructor_id', auth()->id());
                                    }

                                    return $query;
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select lesson'),

                        Forms\Components\TextInput::make('title')
                            ->label('Module Title')
                            ->placeholder('Example: Maombi ni Mazungumzo na Mungu')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Module Description')
                            ->placeholder('Short explanation of what this module covers.')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('order')
                            ->label('Order')
                            ->numeric()
                            ->default(1)
                            ->required(),

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
            ->columns([

                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Module Title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(60)
                    ->wrap()
                    ->placeholder('No description')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('topics_count')
                    ->label('Topics')
                    ->counts('topics')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),

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

                Tables\Filters\SelectFilter::make('lesson_id')
                    ->label('Lesson')
                    ->relationship(
                        name: 'lesson',
                        titleAttribute: 'title',
                        modifyQueryUsing: function (Builder $query) {
                            if (auth()->user()?->role === 'instructor') {
                                return $query->where('instructor_id', auth()->id());
                            }

                            return $query;
                        }
                    )
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),

            ])
            ->defaultSort('order')
            ->actions([

                Tables\Actions\EditAction::make()
                    ->label('Manage Topics'),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),

            ])
            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with('lesson');

        if (auth()->user()?->role === 'instructor') {
            return $query->whereHas('lesson', function (Builder $lessonQuery) {
                $lessonQuery->where('instructor_id', auth()->id());
            });
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            TopicsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }
}