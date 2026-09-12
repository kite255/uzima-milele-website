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

    protected static ?string $navigationLabel = 'Tafakari';

    protected static ?string $modelLabel = 'Tafakari';

    protected static ?string $pluralModelLabel = 'Tafakari';

    protected static ?string $navigationGroup = 'Usimamizi wa Maudhui';

    protected static ?int $navigationSort = 1;

    /*
    |--------------------------------------------------------------------------
    | Admin Pekee
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
    | Fomu
    |--------------------------------------------------------------------------
    */

    public static function form(Form $form): Form
    {
        return $form->schema([

            /*
            |--------------------------------------------------------------------------
            | Taarifa za Msingi
            |--------------------------------------------------------------------------
            */

            Section::make('Taarifa za Msingi')
                ->description(
                    'Weka taarifa kuu za tafakari hii.'
                )
                ->schema([

                    TextInput::make('title')
                        ->label('Kichwa cha Tafakari')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            function (
                                $state,
                                callable $set
                            ): void {
                                if (filled($state)) {
                                    $set(
                                        'slug',
                                        Str::slug($state)
                                    );
                                }
                            }
                        ),

                    TextInput::make('slug')
                        ->label('Kiungo (Slug)')
                        ->required()
                        ->maxLength(255)
                        ->unique(
                            table: 'devotions',
                            column: 'slug',
                            ignoreRecord: true
                        )
                        ->helperText(
                            'Hiki ndicho kitambulisho kinachotumika kwenye anuani ya tafakari.'
                        ),

                    DatePicker::make('published_at')
                        ->label('Tarehe ya Kuchapishwa')
                        ->required(),

                    TimePicker::make('email_send_time')
                        ->label('Muda wa Kutuma Barua Pepe')
                        ->seconds(false)
                        ->native(false)
                        ->default(
                            fn (): string =>
                                EmailSetting::current()
                                    ->default_devotion_send_time
                        )
                        ->helperText(
                            'Muda wa kawaida unatoka kwenye mipangilio ya barua pepe. Unaweza kuubadilisha kwa tafakari hii.'
                        ),

                    FileUpload::make('image')
                        ->label('Picha Kuu')
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
                            'Inapendekezwa: 1200 × 700 px. Ukubwa wa juu: 2 MB. Tumia JPG au WebP iliyoboreshwa.'
                        )
                        ->columnSpanFull(),

                ])
                ->columns(2),

            /*
            |--------------------------------------------------------------------------
            | Muundo wa Tafakari
            |--------------------------------------------------------------------------
            */

            Section::make('Muundo wa Tafakari')
                ->description(
                    'Weka kila sehemu ya tafakari kivyake ili ionekane vizuri na kwa mpangilio mmoja kwenye tovuti na barua pepe.'
                )
                ->schema([

                    RichEditor::make('feature_text')
                        ->label('Ujumbe wa Leo')
                        ->helperText(
                            'Andika ujumbe mkuu wa tafakari hapa.'
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
                            'Andika funzo la vitendo au namna msomaji anavyoweza kuutumia ujumbe huu katika maisha yake.'
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
                        ->placeholder('Yohana 14:27')
                        ->maxLength(255)
                        ->helperText(
                            'Weka rejea ya Biblia pekee, mfano: Yohana 14:27.'
                        ),

                    Textarea::make('scripture_text')
                        ->label('Aya ya Biblia')
                        ->rows(4)
                        ->maxLength(3000)
                        ->placeholder(
                            'Amani nawaachieni; amani yangu nawapa...'
                        )
                        ->helperText(
                            'Weka maneno ya aya ya Biblia hapa.'
                        )
                        ->columnSpanFull(),

                    Textarea::make('ellen_white_quote')
                        ->label('Nukuu ya Ellen G. White')
                        ->rows(4)
                        ->maxLength(3000)
                        ->placeholder(
                            'Nothing tends more to promote health of body and of soul than does a spirit of gratitude and praise.'
                        )
                        ->helperText(
                            'Weka nukuu ya Ellen G. White pekee.'
                        )
                        ->columnSpanFull(),

                    TextInput::make('ellen_white_reference')
                        ->label('Rejea ya Ellen G. White')
                        ->placeholder(
                            'The Ministry of Healing, p. 251.'
                        )
                        ->maxLength(255)
                        ->helperText(
                            'Weka jina la kitabu au chanzo cha nukuu pamoja na ukurasa inapohitajika.'
                        )
                        ->columnSpanFull(),

                ])
                ->columns(2),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Jedwali
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('image')
                    ->label('Picha')
                    ->disk('public')
                    ->height(50)
                    ->width(50)
                    ->square(),

                TextColumn::make('title')
                    ->label('Kichwa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('scripture_reference')
                    ->label('Rejea ya Biblia')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('Tarehe ya Kuchapishwa')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('email_send_time')
                    ->label('Muda wa Barua')
                    ->placeholder('—')
                    ->toggleable(),

                /*
                |--------------------------------------------------------------------------
                | Hali ya Tafakari
                |--------------------------------------------------------------------------
                */

                TextColumn::make('status')
                    ->label('Hali')
                    ->badge()
                    ->getStateUsing(
                        function (
                            Devotion $record
                        ): string {
                            if (
                                blank(
                                    $record->published_at
                                )
                            ) {
                                return 'Haijapangiwa';
                            }

                            $today =
                                now(
                                    'Africa/Dar_es_Salaam'
                                )->toDateString();

                            $publishDate =
                                \Carbon\Carbon::parse(
                                    $record->published_at
                                )
                                    ->timezone(
                                        'Africa/Dar_es_Salaam'
                                    )
                                    ->toDateString();

                            if (
                                $publishDate
                                === $today
                            ) {
                                return 'Leo';
                            }

                            if (
                                $publishDate
                                < $today
                            ) {
                                return 'Imechapishwa';
                            }

                            return 'Ijayo';
                        }
                    )
                    ->color(
                        function (
                            string $state
                        ): string {
                            return match ($state) {
                                'Leo' =>
                                    'warning',

                                'Imechapishwa' =>
                                    'success',

                                'Ijayo' =>
                                    'info',

                                default =>
                                    'gray',
                            };
                        }
                    )
                    ->icon(
                        function (
                            string $state
                        ): string {
                            return match ($state) {
                                'Leo' =>
                                    'heroicon-o-sun',

                                'Imechapishwa' =>
                                    'heroicon-o-check-circle',

                                'Ijayo' =>
                                    'heroicon-o-clock',

                                default =>
                                    'heroicon-o-minus-circle',
                            };
                        }
                    ),

                TextColumn::make('slug')
                    ->label('Kiungo (Slug)')
                    ->searchable()
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),

                TextColumn::make('created_at')
                    ->label('Tarehe ya Kuundwa')
                    ->dateTime('d M Y, H:i')
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

                /*
                |--------------------------------------------------------------------------
                | Fungua Tafakari ya Umma
                |--------------------------------------------------------------------------
                */

                Tables\Actions\Action::make(
                    'viewPublic'
                )
                    ->label(
                        'Fungua Tafakari'
                    )
                    ->icon(
                        'heroicon-o-arrow-top-right-on-square'
                    )
                    ->color('primary')
                    ->url(
                        fn (
                            Devotion $record
                        ): string =>
                            route(
                                'devotions.show',
                                $record->slug
                            )
                    )
                    ->visible(
                        function (
                            Devotion $record
                        ): bool {
                            if (
                                blank(
                                    $record
                                        ->published_at
                                )
                            ) {
                                return false;
                            }

                            return \Carbon\Carbon::parse(
                                $record
                                    ->published_at
                            )
                                ->timezone(
                                    'Africa/Dar_es_Salaam'
                                )
                                ->lte(
                                    now(
                                        'Africa/Dar_es_Salaam'
                                    )
                                );
                        }
                    )
                    ->openUrlInNewTab(),

                /*
                |--------------------------------------------------------------------------
                | Hakiki Barua Pepe
                |--------------------------------------------------------------------------
                */

                Tables\Actions\Action::make(
                    'previewEmail'
                )
                    ->label(
                        'Hakiki Barua Pepe'
                    )
                    ->icon(
                        'heroicon-o-envelope'
                    )
                    ->color('gray')
                    ->url(
                        fn (
                            Devotion $record
                        ): string =>
                            route(
                                'devotions.email.preview',
                                $record
                            )
                    )
                    ->openUrlInNewTab(),

                /*
                |--------------------------------------------------------------------------
                | Hariri
                |--------------------------------------------------------------------------
                */

                Tables\Actions\EditAction::make()
                    ->label('Hariri'),

                /*
                |--------------------------------------------------------------------------
                | Futa
                |--------------------------------------------------------------------------
                */

                Tables\Actions\DeleteAction::make()
                    ->label('Futa')
                    ->modalHeading(
                        'Futa Tafakari'
                    )
                    ->modalDescription(
                        'Una uhakika unataka kufuta tafakari hii? Kitendo hiki hakiwezi kutenduliwa.'
                    )
                    ->modalSubmitActionLabel(
                        'Ndiyo, Futa'
                    )
                    ->modalCancelActionLabel(
                        'Ghairi'
                    )
                    ->requiresConfirmation(),

            ])

            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([

                    Tables\Actions\DeleteBulkAction::make()
                        ->label(
                            'Futa Zilizochaguliwa'
                        )
                        ->modalHeading(
                            'Futa Tafakari Zilizochaguliwa'
                        )
                        ->modalDescription(
                            'Una uhakika unataka kufuta tafakari zote ulizochagua? Kitendo hiki hakiwezi kutenduliwa.'
                        )
                        ->modalSubmitActionLabel(
                            'Ndiyo, Futa'
                        )
                        ->modalCancelActionLabel(
                            'Ghairi'
                        ),

                ]),

            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Mahusiano
    |--------------------------------------------------------------------------
    */

    public static function getRelations(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | Kurasa
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [
            'index' =>
                Pages\ListDevotions::route(
                    '/'
                ),

            'create' =>
                Pages\CreateDevotion::route(
                    '/create'
                ),

            'edit' =>
                Pages\EditDevotion::route(
                    '/{record}/edit'
                ),
        ];
    }
}