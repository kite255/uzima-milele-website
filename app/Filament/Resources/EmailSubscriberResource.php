<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailSubscriberResource\Pages;
use App\Models\EmailSubscriber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EmailSubscriberResource extends Resource
{
    protected static ?string $model = EmailSubscriber::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Wasajili wa Barua Pepe';

    protected static ?string $modelLabel = 'Msajili';

    protected static ?string $pluralModelLabel = 'Wasajili wa Barua Pepe';

    protected static ?string $navigationGroup = 'Barua Pepe';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Taarifa za Msajili')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Jina Kamili')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('email')
                            ->label('Barua Pepe')
                            ->email()
                            ->required()
                            ->unique(
                                table: EmailSubscriber::class,
                                column: 'email',
                                ignoreRecord: true
                            )
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Namba ya Simu')
                            ->tel()
                            ->maxLength(30),

                        Forms\Components\Select::make('status')
                            ->label('Hali')
                            ->options([
                                'subscribed' => 'Amejiandikisha',
                                'unsubscribed' => 'Amejiondoa',
                            ])
                            ->required()
                            ->default('subscribed'),

                        Forms\Components\TextInput::make('source')
                            ->label('Chanzo')
                            ->default('admin')
                            ->maxLength(100),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Taarifa za Usajili')
                    ->schema([
                        Forms\Components\DateTimePicker::make('subscribed_at')
                            ->label('Tarehe ya Kujiandikisha')
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('unsubscribed_at')
                            ->label('Tarehe ya Kujiondoa')
                            ->seconds(false),

                        Forms\Components\TextInput::make('unsubscribe_token')
                            ->label('Unsubscribe Token')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Jina')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Barua Pepe')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Simu')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Hali')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'subscribed' => 'Amejiandikisha',
                            'unsubscribed' => 'Amejiondoa',
                            default => ucfirst((string) $state),
                        }
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'subscribed' => 'success',
                            'unsubscribed' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('Chanzo')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subscribed_at')
                    ->label('Alijiandikisha')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('unsubscribed_at')
                    ->label('Alijiondoa')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aliundwa')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Hali')
                    ->options([
                        'subscribed' => 'Waliojiandikisha',
                        'unsubscribed' => 'Waliojiondoa',
                    ]),

                Tables\Filters\SelectFilter::make('source')
                    ->label('Chanzo')
                    ->options(
                        fn (): array => EmailSubscriber::query()
                            ->whereNotNull('source')
                            ->where('source', '!=', '')
                            ->distinct()
                            ->orderBy('source')
                            ->pluck('source', 'source')
                            ->all()
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Hariri'),

                Tables\Actions\Action::make('subscribe')
                    ->label('Jiandikishe')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(
                        fn (EmailSubscriber $record): bool =>
                            $record->status !== 'subscribed'
                    )
                    ->action(function (EmailSubscriber $record): void {
                        $record->update([
                            'status' => 'subscribed',
                            'subscribed_at' => now(),
                            'unsubscribed_at' => null,
                        ]);
                    }),

                Tables\Actions\Action::make('unsubscribe')
                    ->label('Mtoe')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(
                        fn (EmailSubscriber $record): bool =>
                            $record->status === 'subscribed'
                    )
                    ->action(function (EmailSubscriber $record): void {
                        $record->update([
                            'status' => 'unsubscribed',
                            'unsubscribed_at' => now(),
                        ]);
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Futa'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('subscribe')
                        ->label('Waandikishe')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(
                                fn (EmailSubscriber $record) =>
                                    $record->update([
                                        'status' => 'subscribed',
                                        'subscribed_at' => now(),
                                        'unsubscribed_at' => null,
                                    ])
                            );
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('unsubscribe')
                        ->label('Waondoe')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(
                                fn (EmailSubscriber $record) =>
                                    $record->update([
                                        'status' => 'unsubscribed',
                                        'unsubscribed_at' => now(),
                                    ])
                            );
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Futa waliochaguliwa'),
                ]),
            ])
            ->emptyStateHeading('Hakuna wasajili')
            ->emptyStateDescription(
                'Wasajili wa barua pepe wataonekana hapa baada ya kujiandikisha.'
            )
            ->emptyStateIcon('heroicon-o-envelope');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailSubscribers::route('/'),
            'create' => Pages\CreateEmailSubscriber::route('/create'),
            'edit' => Pages\EditEmailSubscriber::route('/{record}/edit'),
        ];
    }
}