<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    /**
     * Uzima Milele custom admin login view.
     */
    protected static string $view = 'filament.pages.auth.login';

    /**
     * Page heading.
     */
    public function getHeading(): string
    {
        return 'Karibu Tena';
    }

    /**
     * Page subtitle.
     */
    public function getSubheading(): ?string
    {
        return 'Ingia kwenye akaunti yako kuendelea.';
    }

    /**
     * Email field.
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Barua pepe')
            ->email()
            ->required()
            ->markAsRequired(false)
            ->autocomplete('username')
            ->autofocus()
            ->placeholder('Weka barua pepe yako')
            ->prefixIcon('heroicon-o-envelope')
            ->extraAttributes([
                'class' => 'um-master-field um-email-field',
            ])
            ->extraInputAttributes([
                'class' => 'um-master-input',
                'tabindex' => 1,
            ]);
    }

    /**
     * Password field.
     */
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Nenosiri')
            ->password()
            ->required()
            ->markAsRequired(false)
            ->revealable()
            ->autocomplete('current-password')
            ->placeholder('Weka nenosiri lako')
            ->prefixIcon('heroicon-o-lock-closed')
            ->extraAttributes([
                'class' => 'um-master-field um-password-field',
            ])
            ->extraInputAttributes([
                'class' => 'um-master-input',
                'tabindex' => 2,
            ]);
    }

    /**
     * Remember me checkbox.
     */
    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Nikumbuke')
            ->extraAttributes([
                'class' => 'um-master-remember',
            ])
            ->extraInputAttributes([
                'tabindex' => 3,
            ]);
    }
}