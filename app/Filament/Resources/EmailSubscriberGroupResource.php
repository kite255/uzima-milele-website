<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailSubscriberGroupResource\Pages;
use App\Models\EmailSubscriber;
use App\Models\EmailSubscriberGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailSubscriberGroupResource extends Resource
{
    protected static ?string $model = EmailSubscriberGroup::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Vikundi vya Wasajili';

    protected static ?string $modelLabel = 'Kundi la Wasajili';

    protected static ?string $pluralModelLabel = 'Vikundi vya Wasajili';

    protected static ?string $navigationGroup = 'Barua Pepe';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Taarifa za Kundi')
                    ->description(
                        'Tengeneza kundi la wasajili ambalo linaweza kutumika kwenye kampeni za barua pepe.'
                    )
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Jina la Kundi')
                            ->placeholder('Mfano: Morning Devotion')
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                ignoreRecord: true
                            )
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Maelezo')
                            ->placeholder(
                                'Maelezo mafupi kuhusu kundi hili.'
                            )
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Wanachama wa Kundi')
                    ->description(
                        'Chagua wasajili ambao watakuwa sehemu ya kundi hili.'
                    )
                    ->schema([
                        Forms\Components\Select::make('subscribers')
                            ->label('Chagua Wasajili')
                            ->relationship(
                                name: 'subscribers',
                                titleAttribute: 'email',
                                modifyQueryUsing:
                                    fn (Builder $query): Builder =>
                                        $query
                                            ->orderBy('email')
                            )
                            ->getOptionLabelFromRecordUsing(
                                function (
                                    EmailSubscriber $record
                                ): string {
                                    $name = trim(
                                        $record->name
                                        ?: (
                                            ($record->first_name ?? '')
                                            . ' '
                                            . ($record->last_name ?? '')
                                        )
                                    );

                                    if ($name !== '') {
                                        return $name
                                            . ' — '
                                            . $record->email;
                                    }

                                    return $record->email;
                                }
                            )
                            ->multiple()
                            ->searchable([
                                'name',
                                'first_name',
                                'last_name',
                                'email',
                            ])
                            ->preload()
                            ->native(false)
                            ->helperText(
                                'Unaweza kuchagua wasajili wengi kwa wakati mmoja.'
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
                    ->label('Jina la Kundi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Maelezo')
                    ->limit(50)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('subscribers_count')
                    ->label('Wanachama')
                    ->counts('subscribers')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Imeundwa')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
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
            ->emptyStateHeading(
                'Hakuna vikundi vya wasajili'
            )
            ->emptyStateDescription(
                'Tengeneza kundi lako la kwanza la wasajili.'
            )
            ->emptyStateIcon(
                'heroicon-o-user-group'
            );
    }

    public static function getPages(): array
    {
        return [
            'index' =>
                Pages\ListEmailSubscriberGroups::route('/'),

            'create' =>
                Pages\CreateEmailSubscriberGroup::route('/create'),

            'edit' =>
                Pages\EditEmailSubscriberGroup::route('/{record}/edit'),
        ];
    }
}