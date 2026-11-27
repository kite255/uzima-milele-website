<?php
namespace App\Filament\Resources;
use App\Filament\Resources\LaunchAttendeeResource\Pages;
use App\Models\LaunchAttendee;
use App\Jobs\SendLaunchWelcome;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
class LaunchAttendeeResource extends Resource {
    protected static ?string $model = LaunchAttendee::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'App Launch Attendees';
    public static function canViewAny(): bool { return auth()->user()?->role === 'admin'; }
    public static function shouldRegisterNavigation(): bool { return static::canViewAny(); }
    public static function canCreate(): bool { return false; }
    public static function canEdit(Model $record): bool { return false; }
    public static function canDelete(Model $record): bool { return static::canViewAny(); }
    public static function canDeleteAny(): bool { return false; }
    public static function table(Table $table): Table {
        return $table->defaultSort('created_at','desc')->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('phone')->searchable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\TextColumn::make('gender')->toggleable(),
            Tables\Columns\TextColumn::make('church')->searchable(),
            Tables\Columns\TextColumn::make('college')->searchable(),
            Tables\Columns\IconColumn::make('downloaded')->boolean(),
            Tables\Columns\TextColumn::make('sms_status')->badge(),
            Tables\Columns\TextColumn::make('email_status')->badge(),
            Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
        ])->filters([
            Tables\Filters\TernaryFilter::make('downloaded'),
            Tables\Filters\SelectFilter::make('church')->options(fn()=>LaunchAttendee::whereNotNull('church')->distinct()->pluck('church','church')->all()),
            Tables\Filters\SelectFilter::make('college')->options(fn()=>LaunchAttendee::whereNotNull('college')->distinct()->pluck('college','college')->all()),
        ])->actions([
            Tables\Actions\Action::make('retrySms')->label('Queue pending SMS')->visible(fn(LaunchAttendee $record)=>$record->sms_status==='pending')->action(function(LaunchAttendee $record){abort_unless(static::canViewAny(),403);SendLaunchWelcome::dispatch($record->id,'sms')->onQueue('launch-welcome');}),
            Tables\Actions\Action::make('retryEmail')->label('Queue pending email')->visible(fn(LaunchAttendee $record)=>$record->email_status==='pending')->action(function(LaunchAttendee $record){abort_unless(static::canViewAny(),403);SendLaunchWelcome::dispatch($record->id,'email')->onQueue('launch-welcome');}),
            Tables\Actions\DeleteAction::make()->requiresConfirmation(),
        ]);
    }
    public static function getPages(): array { return ['index'=>Pages\ListLaunchAttendees::route('/')]; }
}
