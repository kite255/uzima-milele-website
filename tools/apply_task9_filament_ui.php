<?php

$root = dirname(__DIR__);

function replaceOnce(string $path, string $search, string $replace): void
{
    $contents = file_get_contents($path);

    if ($contents === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    if (! str_contains($contents, $search)) {
        throw new RuntimeException("Expected source fragment not found in {$path}");
    }

    $updated = str_replace($search, $replace, $contents, $count);

    if ($count !== 1) {
        throw new RuntimeException("Expected exactly one source replacement in {$path}; got {$count}");
    }

    file_put_contents($path, $updated);
}

$campaign = $root.'/app/Filament/Resources/EmailCampaignResource.php';
$subscriber = $root.'/app/Filament/Resources/EmailSubscriberResource.php';

replaceOnce(
    $campaign,
    "use App\\Services\\EmailCampaignService;\n",
    "use App\\Services\\Email\\CampaignCloneService;\n"
    ."use App\\Services\\Email\\CampaignExportService;\n"
    ."use App\\Services\\Email\\CampaignSendingService;\n"
    ."use App\\Services\\EmailCampaignService;\n"
);

$advancedActions = <<<'PHP'
                /*
                |--------------------------------------------------------------------------
                | ADVANCED LIFECYCLE ACTIONS
                |--------------------------------------------------------------------------
                */

                Tables\Actions\Action::make('pause')
                    ->label('Sitisha kwa Muda')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->visible(
                        fn (EmailCampaign $record): bool =>
                            in_array('pause', self::availableActionsFor($record), true)
                    )
                    ->requiresConfirmation()
                    ->action(function (EmailCampaign $record): void {
                        app(CampaignSendingService::class)->pause($record);

                        Notification::make()
                            ->title('Kampeni imesitishwa kwa muda')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('resume')
                    ->label('Endelea Kutuma')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(
                        fn (EmailCampaign $record): bool =>
                            in_array('resume', self::availableActionsFor($record), true)
                    )
                    ->requiresConfirmation()
                    ->action(function (EmailCampaign $record): void {
                        app(CampaignSendingService::class)->resume($record);

                        Notification::make()
                            ->title('Kampeni imeendelea kutumwa')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Ghairi Kampeni')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(
                        fn (EmailCampaign $record): bool =>
                            in_array('cancel', self::availableActionsFor($record), true)
                    )
                    ->requiresConfirmation()
                    ->action(function (EmailCampaign $record): void {
                        app(CampaignSendingService::class)->cancel($record);

                        Notification::make()
                            ->title('Kampeni imeghairiwa')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('duplicate')
                    ->label('Nakili Kampeni')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->visible(
                        fn (EmailCampaign $record): bool =>
                            in_array('duplicate', self::availableActionsFor($record), true)
                    )
                    ->action(function (EmailCampaign $record): void {
                        $duplicate = app(CampaignCloneService::class)
                            ->duplicate($record, auth()->id());

                        Notification::make()
                            ->title('Nakala ya kampeni imeundwa')
                            ->body('Rasimu mpya: '.$duplicate->name)
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('resend_non_openers')
                    ->label('Tuma Tena kwa Wasiofungua')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(
                        fn (EmailCampaign $record): bool =>
                            in_array('resend_non_openers', self::availableActionsFor($record), true)
                    )
                    ->requiresConfirmation()
                    ->action(function (EmailCampaign $record): void {
                        $resend = app(CampaignCloneService::class)
                            ->resendToNonOpeners($record, auth()->id());

                        Notification::make()
                            ->title('Rasimu ya kutuma tena imeundwa')
                            ->body('Hariri kichwa au maudhui kabla ya kutuma: '.$resend->name)
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('retry_failed')
                    ->label('Jaribu Tena Zilizoshindwa')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('danger')
                    ->visible(
                        fn (EmailCampaign $record): bool =>
                            in_array('retry_failed', self::availableActionsFor($record), true)
                    )
                    ->action(function (EmailCampaign $record): void {
                        $retry = app(CampaignCloneService::class)
                            ->retryFailed($record, auth()->id());

                        Notification::make()
                            ->title('Rasimu ya kujaribu tena imeundwa')
                            ->body($retry->name)
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('export')
                    ->label('Pakua CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(
                        fn (EmailCampaign $record): bool =>
                            in_array('export', self::availableActionsFor($record), true)
                    )
                    ->action(
                        fn (EmailCampaign $record) =>
                            app(CampaignExportService::class)->csv($record)
                    ),

PHP;

replaceOnce(
    $campaign,
    "                /*\n                |--------------------------------------------------------------------------\n                | DELETE\n                |--------------------------------------------------------------------------\n                */\n",
    $advancedActions
    ."                /*\n                |--------------------------------------------------------------------------\n                | DELETE\n                |--------------------------------------------------------------------------\n                */\n"
);

replaceOnce(
    $subscriber,
    "use App\\Models\\EmailSubscriber;\n",
    "use App\\Models\\EmailSubscriber;\nuse App\\Services\\Email\\EmailAddressHygieneService;\n"
);

$emailFieldBefore = <<<'PHP'
                        Forms\Components\TextInput::make('email')
                            ->label('Barua Pepe')
                            ->email()
                            ->required()
PHP;

$emailFieldAfter = <<<'PHP'
                        Forms\Components\TextInput::make('email')
                            ->label('Barua Pepe')
                            ->email()
                            ->required()
                            ->live(onBlur: true)
                            ->helperText(function (?string $state): ?string {
                                if (blank($state)) {
                                    return null;
                                }

                                $suggestion = app(EmailAddressHygieneService::class)
                                    ->suggestion($state);

                                return $suggestion
                                    ? 'Huenda ulimaanisha: '.$suggestion
                                    : null;
                            })
PHP;

replaceOnce(
    $subscriber,
    $emailFieldBefore,
    $emailFieldAfter
);

echo "Task 9 Filament UI patch applied successfully.\n";
