<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WatotoVideoResource\Pages;
use App\Models\WatotoVideo;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;

class WatotoVideoResource extends Resource
{
    protected static ?string $model = WatotoVideo::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';

    protected static ?string $navigationLabel = 'Watoto Videos';

    protected static ?string $modelLabel = 'Watoto Video';

    protected static ?string $pluralModelLabel = 'Watoto Videos';

    protected static ?string $navigationGroup = 'Content Management';

    /*
    |--------------------------------------------------------------------------
    | Admin Only
    |--------------------------------------------------------------------------
    | Hide Watoto Videos from instructors and block direct URL access.
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
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('youtube_url')
                    ->label('YouTube Link')
                    ->placeholder('https://www.youtube.com/watch?v=...')
                    ->required()
                    ->url()
                    ->maxLength(255),

                TextInput::make('category')
                    ->label('Category')
                    ->placeholder('e.g. Hadithi za Biblia')
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('main_lesson')
                    ->label('Funzo Kuu')
                    ->rows(4)
                    ->columnSpanFull(),

                TextInput::make('bible_verse')
                    ->label('Mstari wa Biblia')
                    ->placeholder('e.g. Mathayo 19:14')
                    ->maxLength(255),

                TextInput::make('reflection_question')
                    ->label('Swali la Kutafakari')
                    ->placeholder('e.g. Je, umejifunza nini?')
                    ->maxLength(255),

                Toggle::make('is_featured')
                    ->label('Featured Video')
                    ->default(false),

                Toggle::make('is_published')
                    ->label('Published')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('youtube_thumbnail')
                    ->label('Thumbnail')
                    ->square()
                    ->size(60),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color('warning')
                    ->searchable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWatotoVideos::route('/'),
            'create' => Pages\CreateWatotoVideo::route('/create'),
            'edit' => Pages\EditWatotoVideo::route('/{record}/edit'),
        ];
    }
}