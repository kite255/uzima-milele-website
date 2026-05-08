<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrayerRequestResource\Pages;
use App\Models\PrayerRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrayerRequestResource extends Resource
{
    protected static ?string $model = PrayerRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?string $navigationLabel = 'Prayer Requests';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Prayer Request Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('contact')
                        ->label('Email / Contact')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('prayer_type')
                        ->label('Subject')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('message')
                        ->required()
                        ->rows(6)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_private')
                        ->label('Private Request'),

                    Forms\Components\Select::make('status')
                        ->options([
                            'new' => 'New',
                            'prayed' => 'Prayed',
                            'closed' => 'Closed',
                        ])
                        ->default('new')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('contact')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('prayer_type')
                    ->label('Subject')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_private')
                    ->label('Private')
                    ->boolean(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'new',
                        'success' => 'prayed',
                        'gray' => 'closed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'prayed' => 'Prayed',
                        'closed' => 'Closed',
                    ]),

                Tables\Filters\TernaryFilter::make('is_private')
                    ->label('Private Requests'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('mark_prayed')
                    ->label('Mark Prayed')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (PrayerRequest $record) => $record->update([
                        'status' => 'prayed',
                    ]))
                    ->visible(fn (PrayerRequest $record) => $record->status !== 'prayed'),

                Tables\Actions\Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(fn (PrayerRequest $record) => $record->update([
                        'status' => 'closed',
                    ]))
                    ->visible(fn (PrayerRequest $record) => $record->status !== 'closed'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrayerRequests::route('/'),
            'create' => Pages\CreatePrayerRequest::route('/create'),
            'view' => Pages\ViewPrayerRequest::route('/{record}'),
            'edit' => Pages\EditPrayerRequest::route('/{record}/edit'),
        ];
    }
}