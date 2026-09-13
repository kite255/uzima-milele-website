<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DevotionResource\Pages;
use App\Models\Devotion;
use App\Models\EmailSetting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DevotionResource extends Resource
{
    protected static ?string $model = Devotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Devotions';

    protected static ?string $modelLabel = 'Devotion';

    protected static ?string $pluralModelLabel = 'Devotions';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 1;

    /*
    |--------------------------------------------------------------------------
    | Admin Only
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    public static function form(Form $form): Form
    {
        return $form->schema([

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            Section::make('Basic Information')
                ->description(
                    'Enter the main information for this devotion.'
                )
                ->schema([

                    TextInput::make('title')
                        ->label('Title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            function ($state, callable $set): void {
                                if (filled($state)) {
                                    $set(
                                        'slug',
                                        Str::slug($state)
                                    );
                                }
                            }
                        ),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(
                            table: 'devotions',
                            column: 'slug',
                            ignoreRecord: true
                        )
                        ->helperText(
                            'Automatically generated from the title. You may edit it if needed.'
                        ),

                    DatePicker::make('published_at')
                        ->label('Publish Date')
                        ->required(),

                    TimePicker::make('email_send_time')
                        ->label('Email Send Time')
                        ->seconds(false)
                        ->native(false)
                        ->default(
                            fn (): string =>
                                EmailSetting::current()
                                    ->default_devotion_send_time
                        )
                        ->helperText(
                            'Defaults to the devotion email time configured in Email Settings. You can override it for this devotion.'
                        ),

                    FileUpload::make('image')
                        ->label('Featured Image')
                        ->image()
                        ->disk('public')
                        ->directory('devotions')
                        ->visibility('public')
                        ->imageEditor()
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('12:7')
                        ->imageResizeTargetWidth('1200')
                        ->imageResizeTargetHeight('700')
                        ->imagePreviewHeight('180')
                        ->maxSize(2048)
                        ->acceptedFileTypes([
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ])
                        ->downloadable()
                        ->openable()
                        ->helperText(
                            'Recommended: 1200 × 700 px. Maximum size: 2 MB. Use JPG, PNG, or WebP.'
                        )
                        ->columnSpanFull(),

                ])
                ->columns(2),

            /*
            |--------------------------------------------------------------------------
            | Structured Devotion
            |--------------------------------------------------------------------------
            */

            Section::make('Structured Devotion')
                ->description(
                    'These fields control the structured devotion page shown to readers.'
                )
                ->schema([

                    RichEditor::make('feature_text')
                        ->label('Ujumbe wa Leo')
                        ->helperText(
                            'Write the main message of the devotion.'
                        )
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'bulletList',
                            'orderedList',
                            'link',
                            'undo',
                            'redo',
                        ])
                        ->columnSpanFull(),

                    RichEditor::make('lesson')
                        ->label('Funzo la Leo')
                        ->helperText(
                            'Write the practical lesson or action the reader should take.'
                        )
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'bulletList',
                            'orderedList',
                            'link',
                            'undo',
                            'redo',
                        ])
                        ->columnSpanFull(),

                    TextInput::make('scripture_reference')
                        ->label('Rejea ya Biblia')
                        ->placeholder('Isaya 40:31')
                        ->maxLength(255)
                        ->helperText(
                            'Enter the Bible reference only.'
                        ),

                    Textarea::make('scripture_text')
                        ->label('Aya ya Biblia')
                        ->placeholder(
                            'Bali wamngojeao Bwana watapata nguvu mpya...'
                        )
                        ->rows(4)
                        ->maxLength(3000)
                        ->helperText(
                            'Enter the Bible verse text.'
                        )
                        ->columnSpanFull(),

                    Textarea::make('ellen_white_quote')
                        ->label('Nukuu ya Ellen G. White')
                        ->placeholder(
                            'Enter the Ellen G. White quotation here.'
                        )
                        ->rows(4)
                        ->maxLength(3000)
                        ->helperText(
                            'Optional quotation from Ellen G. White.'
                        )
                        ->columnSpanFull(),

                    TextInput::make('ellen_white_reference')
                        ->label('Rejea ya Ellen G. White')
                        ->placeholder(
                            'Testimonies for the Church, vol. 9, p. 19.'
                        )
                        ->maxLength(255)
                        ->helperText(
                            'Enter the book/source and page reference.'
                        )
                        ->columnSpanFull(),

                ])
                ->columns(2),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->height(50)
                    ->width(50)
                    ->square(),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('scripture_reference')
                    ->label('Scripture')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                TextColumn::make('published_at')
                    ->label('Publish Date')
                    ->date('d M, Y')
                    ->sortable(),

                TextColumn::make('email_send_time')
                    ->label('Email Send Time')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M, Y')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])
            ->defaultSort(
                'published_at',
                'desc'
            )
            ->actions([

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public static function getRelations(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [
            'index' =>
                Pages\ListDevotions::route('/'),

            'create' =>
                Pages\CreateDevotion::route('/create'),

            'edit' =>
                Pages\EditDevotion::route('/{record}/edit'),
        ];
    }
}