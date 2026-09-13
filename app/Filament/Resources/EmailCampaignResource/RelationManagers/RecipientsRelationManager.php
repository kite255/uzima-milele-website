<?php

namespace App\Filament\Resources\EmailCampaignResource\RelationManagers;

use App\Jobs\SendEmailCampaign;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;

class RecipientsRelationManager extends RelationManager
{
    protected static string $relationship = 'recipients';

    protected static ?string $title = 'Ripoti ya Wapokeaji';

    protected static ?string $modelLabel = 'Mpokeaji';

    protected static ?string $pluralModelLabel = 'Wapokeaji';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->defaultSort('id', 'asc')

            /*
            |--------------------------------------------------------------------------
            | COLUMNS
            |--------------------------------------------------------------------------
            */
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Jina')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Barua Pepe')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Hali')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            EmailCampaignRecipient::STATUS_PENDING => 'Inasubiri',
                            EmailCampaignRecipient::STATUS_SENT => 'Imetumwa',
                            EmailCampaignRecipient::STATUS_FAILED => 'Imeshindwa',
                            default => ucfirst((string) $state),
                        }
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            EmailCampaignRecipient::STATUS_PENDING => 'warning',
                            EmailCampaignRecipient::STATUS_SENT => 'success',
                            EmailCampaignRecipient::STATUS_FAILED => 'danger',
                            default => 'gray',
                        }
                    )
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | OPEN TRACKING
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('first_opened_at')
                    ->label('Imefunguliwa')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            mixed $state,
                            EmailCampaignRecipient $record
                        ): string =>
                            $record->wasOpened()
                                ? 'Ndiyo'
                                : 'Hapana'
                    )
                    ->color(
                        fn (
                            mixed $state,
                            EmailCampaignRecipient $record
                        ): string =>
                            $record->wasOpened()
                                ? 'success'
                                : 'gray'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('first_opened_at')
                    ->label('Mara ya Kwanza')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_opened_at')
                    ->label('Mara ya Mwisho')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('open_count')
                    ->label('Idadi ya Kufunguliwa')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                /*
                |--------------------------------------------------------------------------
                | DELIVERY INFORMATION
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Ilitumwa')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('failed_at')
                    ->label('Imeshindwa Tarehe')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Sababu ya Kushindwa')
                    ->limit(70)
                    ->wrap()
                    ->placeholder('—')
                    ->tooltip(
                        fn (EmailCampaignRecipient $record): ?string =>
                            $record->error_message
                    )
                    ->visible(
                        fn (): bool =>
                            $this->getOwnerRecord()->failed_count > 0
                    ),
            ])

            /*
            |--------------------------------------------------------------------------
            | FILTERS
            |--------------------------------------------------------------------------
            */
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Hali ya Uwasilishaji')
                    ->options([
                        EmailCampaignRecipient::STATUS_PENDING => 'Inasubiri',
                        EmailCampaignRecipient::STATUS_SENT => 'Imetumwa',
                        EmailCampaignRecipient::STATUS_FAILED => 'Imeshindwa',
                    ]),

                Tables\Filters\SelectFilter::make('open_status')
                    ->label('Hali ya Kufunguliwa')
                    ->options([
                        'opened' => 'Imefunguliwa',
                        'unopened' => 'Haijafunguliwa',
                    ])
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            return match (
                                $data['value'] ?? null
                            ) {
                                'opened' =>
                                    $query->whereNotNull(
                                        'first_opened_at'
                                    ),

                                'unopened' =>
                                    $query
                                        ->where(
                                            'status',
                                            EmailCampaignRecipient::STATUS_SENT
                                        )
                                        ->whereNull(
                                            'first_opened_at'
                                        ),

                                default =>
                                    $query,
                            };
                        }
                    ),
            ])

            /*
            |--------------------------------------------------------------------------
            | HEADER ACTIONS
            |--------------------------------------------------------------------------
            */
            ->headerActions([
                Tables\Actions\Action::make('retry_failed')
                    ->label('Jaribu Tena Zilizoshindwa')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(
                        fn (): bool =>
                            $this->getOwnerRecord()->failed_count > 0
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Jaribu Tena Barua Zilizoshindwa')
                    ->modalDescription(
                        'Barua pepe zote zilizoshindwa kwenye kampeni hii zitawekwa tena kwenye foleni. Barua ambazo tayari zimetumwa hazitatumwa tena.'
                    )
                    ->modalSubmitActionLabel('Ndiyo, Jaribu Tena')
                    ->action(function (): void {
                        try {
                            $campaign = $this->getOwnerRecord();

                            DB::transaction(
                                function () use ($campaign): void {
                                    $failedRecipients = EmailCampaignRecipient::query()
                                        ->where(
                                            'email_campaign_id',
                                            $campaign->id
                                        )
                                        ->where(
                                            'status',
                                            EmailCampaignRecipient::STATUS_FAILED
                                        )
                                        ->get();

                                    if ($failedRecipients->isEmpty()) {
                                        throw new \RuntimeException(
                                            'Hakuna barua pepe zilizoshindwa kujaribiwa tena.'
                                        );
                                    }

                                    foreach ($failedRecipients as $recipient) {
                                        $recipient->update([
                                            'status' =>
                                                EmailCampaignRecipient::STATUS_PENDING,
                                            'sent_at' =>
                                                null,
                                            'failed_at' =>
                                                null,
                                            'error_message' =>
                                                null,
                                        ]);
                                    }

                                    $sentCount = EmailCampaignRecipient::query()
                                        ->where(
                                            'email_campaign_id',
                                            $campaign->id
                                        )
                                        ->where(
                                            'status',
                                            EmailCampaignRecipient::STATUS_SENT
                                        )
                                        ->count();

                                    $pendingCount = EmailCampaignRecipient::query()
                                        ->where(
                                            'email_campaign_id',
                                            $campaign->id
                                        )
                                        ->where(
                                            'status',
                                            EmailCampaignRecipient::STATUS_PENDING
                                        )
                                        ->count();

                                    $campaign->update([
                                        'status' =>
                                            EmailCampaign::STATUS_QUEUED,

                                        'sent_count' =>
                                            $sentCount,

                                        'failed_count' =>
                                            0,

                                        'sent_at' =>
                                            null,

                                        'queued_at' =>
                                            now(),
                                    ]);

                                    if ($pendingCount > 0) {
                                        SendEmailCampaign::dispatch(
                                            $campaign->id
                                        )->afterCommit();
                                    }
                                }
                            );

                            Notification::make()
                                ->title(
                                    'Barua zilizoshindwa zimewekwa tena kwenye foleni'
                                )
                                ->body(
                                    'Mfumo utajaribu kutuma tena barua pepe zilizoshindwa tu.'
                                )
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->title(
                                    'Jaribio la kutuma tena limeshindwa'
                                )
                                ->body(
                                    $exception->getMessage()
                                )
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
            ])

            /*
            |--------------------------------------------------------------------------
            | ROW ACTIONS
            |--------------------------------------------------------------------------
            */
            ->actions([
                Tables\Actions\Action::make('view_error')
                    ->label('Tazama Sababu')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(
                        fn (EmailCampaignRecipient $record): bool =>
                            $record->status
                                === EmailCampaignRecipient::STATUS_FAILED
                            && filled($record->error_message)
                    )
                    ->modalHeading(
                        fn (EmailCampaignRecipient $record): string =>
                            'Sababu ya Kushindwa: ' . $record->email
                    )
                    ->modalDescription(
                        'Hii ni taarifa iliyorejeshwa wakati mfumo ulipojaribu kutuma barua pepe.'
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Funga')
                    ->modalContent(
                        fn (EmailCampaignRecipient $record) =>
                            view(
                                'filament.email-campaigns.recipient-error',
                                [
                                    'recipient' => $record,
                                ]
                            )
                    ),

                Tables\Actions\Action::make('retry')
                    ->label('Jaribu Tena')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(
                        fn (EmailCampaignRecipient $record): bool =>
                            $record->status
                                === EmailCampaignRecipient::STATUS_FAILED
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Jaribu Tena Kutuma Barua Pepe')
                    ->modalDescription(
                        fn (EmailCampaignRecipient $record): string =>
                            'Mfumo utajaribu kutuma tena barua pepe kwenda '
                            . $record->email
                            . '.'
                    )
                    ->modalSubmitActionLabel('Ndiyo, Jaribu Tena')
                    ->action(
                        function (
                            EmailCampaignRecipient $record
                        ): void {
                            try {
                                $campaign =
                                    $this->getOwnerRecord();

                                DB::transaction(
                                    function () use (
                                        $campaign,
                                        $record
                                    ): void {
                                        $recipient =
                                            EmailCampaignRecipient::query()
                                                ->lockForUpdate()
                                                ->findOrFail(
                                                    $record->id
                                                );

                                        if (
                                            $recipient->status
                                            !== EmailCampaignRecipient::STATUS_FAILED
                                        ) {
                                            throw new \RuntimeException(
                                                'Mpokeaji huyu hayupo kwenye hali ya kushindwa.'
                                            );
                                        }

                                        $recipient->update([
                                            'status' =>
                                                EmailCampaignRecipient::STATUS_PENDING,

                                            'sent_at' =>
                                                null,

                                            'failed_at' =>
                                                null,

                                            'error_message' =>
                                                null,
                                        ]);

                                        $sentCount =
                                            EmailCampaignRecipient::query()
                                                ->where(
                                                    'email_campaign_id',
                                                    $campaign->id
                                                )
                                                ->where(
                                                    'status',
                                                    EmailCampaignRecipient::STATUS_SENT
                                                )
                                                ->count();

                                        $failedCount =
                                            EmailCampaignRecipient::query()
                                                ->where(
                                                    'email_campaign_id',
                                                    $campaign->id
                                                )
                                                ->where(
                                                    'status',
                                                    EmailCampaignRecipient::STATUS_FAILED
                                                )
                                                ->count();

                                        $campaign->update([
                                            'status' =>
                                                EmailCampaign::STATUS_QUEUED,

                                            'sent_count' =>
                                                $sentCount,

                                            'failed_count' =>
                                                $failedCount,

                                            'sent_at' =>
                                                null,

                                            'queued_at' =>
                                                now(),
                                        ]);

                                        SendEmailCampaign::dispatch(
                                            $campaign->id
                                        )->afterCommit();
                                    }
                                );

                                Notification::make()
                                    ->title(
                                        'Barua pepe imewekwa tena kwenye foleni'
                                    )
                                    ->body(
                                        'Mfumo utajaribu kutuma tena kwenda '
                                        . $record->email
                                        . '.'
                                    )
                                    ->success()
                                    ->send();
                            } catch (Throwable $exception) {
                                report($exception);

                                Notification::make()
                                    ->title(
                                        'Jaribio la kutuma tena limeshindwa'
                                    )
                                    ->body(
                                        $exception->getMessage()
                                    )
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        }
                    ),
            ])

            ->bulkActions([])

            /*
            |--------------------------------------------------------------------------
            | EMPTY STATE
            |--------------------------------------------------------------------------
            */
            ->emptyStateHeading('Hakuna wapokeaji')
            ->emptyStateDescription(
                'Hakuna rekodi za wapokeaji kwa kampeni hii.'
            )
            ->emptyStateIcon('heroicon-o-envelope');
    }
}