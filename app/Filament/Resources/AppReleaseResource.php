<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppReleaseResource\Pages;
use App\Models\AppRelease;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AppReleaseResource extends Resource
{
    protected static ?string $model = AppRelease::class;
    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'App Releases';

    public static function canViewAny(): bool { return auth()->user()?->role === 'admin'; }
    public static function canCreate(): bool { return static::canViewAny(); }
    public static function canEdit(Model $record): bool { return static::canViewAny(); }
    public static function canDelete(Model $record): bool { return false; }
    public static function shouldRegisterNavigation(): bool { return static::canViewAny(); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('version')->required()->maxLength(40)
                ->regex('/^[0-9][a-zA-Z0-9._-]*$/')
                ->placeholder('1.1.0'),
            FileUpload::make('apk_upload')->label('Android APK')
                ->required(fn (string $operation): bool => $operation === 'create')
                ->visible(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (string $operation): bool => $operation === 'create')
                ->storeFiles(false)
                ->previewable(false)
                ->acceptedFileTypes(['application/vnd.android.package-archive', 'application/zip', 'application/octet-stream'])
                ->maxSize(153600)
                ->helperText('Upload a signed release APK, up to 150 MB. Publish from the release list after upload.'),
            Textarea::make('release_notes')->label('Release notes')->maxLength(5000)->rows(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('version')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('size_bytes')->label('Size')
                ->formatStateUsing(fn ($state) => number_format($state / 1048576, 1).' MB'),
            Tables\Columns\TextColumn::make('published_at')->label('Status')
                ->formatStateUsing(fn ($state) => $state ? 'Published' : 'Draft / Archived')
                ->placeholder('Draft / Archived'),
            Tables\Columns\TextColumn::make('downloads')->label('Download requests')->numeric()->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->defaultSort('id', 'desc')->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('publish')->label('Publish')->icon('heroicon-o-check-circle')
                ->visible(fn (AppRelease $record) => !$record->published_at)
                ->requiresConfirmation()
                ->modalDescription('This replaces the current public APK. Older releases remain available for rollback in this admin panel.')
                ->action(function (AppRelease $record): void {
                    abort_unless(static::canViewAny(), 403);
                    if (!$record->fileAvailable()) {
                        Notification::make()->title('APK file is missing')->danger()->send();
                        return;
                    }
                    DB::transaction(function () use ($record): void {
                        AppRelease::query()->orderBy('id')->lockForUpdate()->get();
                        AppRelease::whereNotNull('published_at')->update(['published_at' => null]);
                        $record->update(['published_at' => now()]);
                    });
                    Notification::make()->title('Release published')->success()->send();
                }),
            Tables\Actions\Action::make('unpublish')->label('Unpublish')->color('warning')
                ->visible(fn (AppRelease $record) => (bool) $record->published_at)
                ->requiresConfirmation()
                ->action(function (AppRelease $record): void {
                    abort_unless(static::canViewAny(), 403);
                    $record->update(['published_at' => null]);
                    Notification::make()->title('Release unpublished')->success()->send();
                }),
            Tables\Actions\Action::make('publicPage')->label('Download page')->url(fn () => route('app.download.page'))->openUrlInNewTab(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppReleases::route('/'),
            'create' => Pages\CreateAppRelease::route('/create'),
            'edit' => Pages\EditAppRelease::route('/{record}/edit'),
        ];
    }
}
