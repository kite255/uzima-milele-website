<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailCampaignTemplateResource\Pages;
use App\Models\EmailCampaignTemplate;
use App\Services\Email\CampaignTemplateService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EmailCampaignTemplateResource extends Resource
{
    protected static ?string $model = EmailCampaignTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Violezo vya Kampeni';

    protected static ?string $modelLabel = 'Kiolezo cha Kampeni';

    protected static ?string $pluralModelLabel = 'Violezo vya Kampeni';

    protected static ?string $navigationGroup = 'Barua Pepe';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Taarifa za Kiolezo')
                    ->description(
                        'Hifadhi muundo unaoweza kutumika tena wakati wa kutengeneza kampeni za barua pepe.'
                    )
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Jina la Kiolezo')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                function (?string $state, Forms\Set $set): void {
                                    if (filled($state)) {
                                        $set('slug', Str::slug($state));
                                    }
                                }
                            ),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('type')
                            ->label('Aina ya Kiolezo')
                            ->options([
                                CampaignTemplateService::TYPE_DEVOTION => 'Tafakari',
                                CampaignTemplateService::TYPE_CHILDREN_DEVOTION => 'Tafakari ya Watoto',
                                CampaignTemplateService::TYPE_NEWSLETTER => 'Newsletter',
                                CampaignTemplateService::TYPE_ANNOUNCEMENT => 'Tangazo',
                                CampaignTemplateService::TYPE_LESSON_REMINDER => 'Kikumbusho cha Somo',
                                CampaignTemplateService::TYPE_SPECIAL_EVENT => 'Tukio Maalum',
                                CampaignTemplateService::TYPE_GENERAL => 'Jumla',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Kiolezo Kinatumika')
                            ->default(true),

                        Forms\Components\TextInput::make('subject')
                            ->label('Kichwa cha Barua Pepe')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->label('Maudhui ya Kiolezo')
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Vibadilika vinavyoruhusiwa')
                    ->description(
                        'Unaweza kutumia placeholders hizi kwenye kichwa au maudhui ya kampeni.'
                    )
                    ->schema([
                        Forms\Components\Placeholder::make('supported_placeholders')
                            ->label('Placeholders')
                            ->content(
                                '{{first_name}}, {{last_name}}, {{name}}, {{email}}, {{language}}, '
                                . '{{campaign_name}}, {{campaign_subject}}, {{devotion_title}}, '
                                . '{{devotion_url}}, {{unsubscribe_url}}'
                            )
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Kiolezo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Aina')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            CampaignTemplateService::TYPE_DEVOTION => 'Tafakari',
                            CampaignTemplateService::TYPE_CHILDREN_DEVOTION => 'Watoto',
                            CampaignTemplateService::TYPE_NEWSLETTER => 'Newsletter',
                            CampaignTemplateService::TYPE_ANNOUNCEMENT => 'Tangazo',
                            CampaignTemplateService::TYPE_LESSON_REMINDER => 'Kikumbusho cha Somo',
                            CampaignTemplateService::TYPE_SPECIAL_EVENT => 'Tukio Maalum',
                            CampaignTemplateService::TYPE_GENERAL => 'Jumla',
                            default => $state,
                        }
                    ),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Kichwa')
                    ->searchable()
                    ->limit(55),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Kinatumika')
                    ->boolean(),

                Tables\Columns\TextColumn::make('campaigns_count')
                    ->label('Matumizi')
                    ->counts('campaigns')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Imeundwa')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Aina')
                    ->options([
                        CampaignTemplateService::TYPE_DEVOTION => 'Tafakari',
                        CampaignTemplateService::TYPE_CHILDREN_DEVOTION => 'Tafakari ya Watoto',
                        CampaignTemplateService::TYPE_NEWSLETTER => 'Newsletter',
                        CampaignTemplateService::TYPE_ANNOUNCEMENT => 'Tangazo',
                        CampaignTemplateService::TYPE_LESSON_REMINDER => 'Kikumbusho cha Somo',
                        CampaignTemplateService::TYPE_SPECIAL_EVENT => 'Tukio Maalum',
                        CampaignTemplateService::TYPE_GENERAL => 'Jumla',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Kinatumika'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Hariri'),

                Tables\Actions\DeleteAction::make()
                    ->label('Futa')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Hakuna violezo vya kampeni')
            ->emptyStateDescription(
                'Tengeneza kiolezo cha kwanza ili ukitumie tena kwenye kampeni za barua pepe.'
            )
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailCampaignTemplates::route('/'),
            'create' => Pages\CreateEmailCampaignTemplate::route('/create'),
            'edit' => Pages\EditEmailCampaignTemplate::route('/{record}/edit'),
        ];
    }
}
