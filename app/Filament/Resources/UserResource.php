<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $navigationGroup = 'System Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

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
        return auth()->user()?->role === 'admin'
            && $record->id !== auth()->id();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('User Information')
                    ->description('Manage user account details and contact information.')
                    ->schema([

                        Forms\Components\FileUpload::make('avatar')
                            ->label('Avatar')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('users/avatars')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('400')
                            ->imageResizeTargetHeight('400')
                            ->maxSize(1024)
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->downloadable()
                            ->openable()
                            ->helperText('Optional. Max 1MB. Recommended 400 x 400 px.'),

                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Example: Kitenken Lucas'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(
                                table: 'users',
                                column: 'email',
                                ignoreRecord: true
                            )
                            ->placeholder('example@email.com'),

                        Forms\Components\TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('255768461644')
                            ->helperText('Use international format for SMS reminders. Example: 255768461644'),

                        Forms\Components\Select::make('role')
                            ->label('User Role')
                            ->required()
                            ->default('student')
                            ->options([
                                'admin' => 'Admin',
                                'instructor' => 'Instructor',
                                'student' => 'Student',
                            ])
                            ->helperText('Admin can access everything. Instructor can manage assigned lessons. Student uses the learning dashboard.'),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->seconds(false)
                            ->helperText('Leave empty if email is not verified.'),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Password')
                    ->description('Set password for new users or update password for existing users.')
                    ->schema([

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Leave empty when editing if you do not want to change the password.'),

                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'User')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->placeholder('No name'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->placeholder('No phone'),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'instructor' => 'Instructor',
                        'student' => 'Student',
                        default => 'Unknown',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'admin' => 'danger',
                        'instructor' => 'warning',
                        'student' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => filled($record->email_verified_at)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('d M, Y')
                    ->sortable(),

            ])
            ->filters([

                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'instructor' => 'Instructor',
                        'student' => 'Student',
                    ]),

                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->nullable(),

            ])
            ->actions([

                Tables\Actions\Action::make('verifyEmail')
                    ->label('Verify Email')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (User $record): bool => blank($record->email_verified_at))
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update([
                            'email_verified_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Email verified')
                            ->body("{$record->email} has been marked as verified.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('makeStudent')
                    ->label('Make Student')
                    ->icon('heroicon-o-user')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->role !== 'student')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update([
                            'role' => 'student',
                        ]);

                        Notification::make()
                            ->title('Role updated')
                            ->body("{$record->name} is now a student.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('makeInstructor')
                    ->label('Make Instructor')
                    ->icon('heroicon-o-academic-cap')
                    ->color('warning')
                    ->visible(fn (User $record): bool => $record->role !== 'instructor')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update([
                            'role' => 'instructor',
                        ]);

                        Notification::make()
                            ->title('Role updated')
                            ->body("{$record->name} is now an instructor.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (User $record): bool => $record->id !== auth()->id())
                    ->requiresConfirmation(),

            ])
            ->bulkActions([

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}