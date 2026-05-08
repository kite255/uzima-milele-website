<?php

namespace App\Filament\Resources\LessonResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    protected static ?string $title = 'Course Modules';

    protected static ?string $modelLabel = 'Module';

    protected static ?string $pluralModelLabel = 'Modules';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('title')
                    ->label('Module Title')
                    ->placeholder('Example: Maana ya Maombi')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

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
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([

                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Module Title')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(70)
                    ->wrap()
                    ->placeholder('No description'),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M, Y')
                    ->sortable(),
            ])
            ->defaultSort('order')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Module'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit Module'),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}