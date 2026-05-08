<?php

namespace App\Filament\Resources\ModuleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TopicsRelationManager extends RelationManager
{
    protected static string $relationship = 'topics';

    protected static ?string $title = 'Module Topics';

    protected static ?string $modelLabel = 'Topic';

    protected static ?string $pluralModelLabel = 'Topics';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Topic Information')
                    ->description('Create learning topics inside this module.')
                    ->schema([

                        Forms\Components\TextInput::make('title')
                            ->label('Topic Title')
                            ->placeholder('Example: Maana ya Maombi')
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
                            ->placeholder('maana-ya-maombi')
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
                            ->label('Topic PDF')
                            ->disk('public')
                            ->directory('lessons/topics/pdfs')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->downloadable()
                            ->openable()
                            ->preserveFilenames()
                            ->helperText('Optional downloadable PDF for this topic.'),

                        Forms\Components\RichEditor::make('content')
                            ->label('Topic Content')
                            ->placeholder('Write the full learning content for this topic here.')
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
                            ->label('Free Preview Topic')
                            ->default(false)
                            ->onColor('success')
                            ->helperText('Allow visitors to preview this topic before enrollment.'),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(true)
                            ->onColor('success'),

                    ])
                    ->columns(2),

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
                    ->label('Topic Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('video_url')
                    ->label('Video')
                    ->placeholder('No video')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\IconColumn::make('pdf')
                    ->label('PDF')
                    ->boolean()
                    ->getStateUsing(fn ($record) => filled($record->pdf)),

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
            ->defaultSort('order')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Topic')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['slug']) && ! empty($data['title'])) {
                            $data['slug'] = Str::slug($data['title']);
                        }

                        return $data;
                    }),
            ])
            ->actions([

                Tables\Actions\EditAction::make()
                    ->label('Edit Topic')
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['slug']) && ! empty($data['title'])) {
                            $data['slug'] = Str::slug($data['title']);
                        }

                        return $data;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}