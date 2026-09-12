<?php

namespace App\Filament\Pages;

use App\Models\EmailCampaign;
use App\Models\EmailSetting;
use App\Models\EmailSubscriberGroup;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class EmailSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon =
        'heroicon-o-envelope';

    protected static ?string $navigationLabel =
        'Mipangilio ya Barua Pepe';

    protected static ?string $title =
        'Mipangilio ya Barua Pepe';

    protected static ?string $navigationGroup =
        'Barua Pepe';

    protected static ?int $navigationSort = 4;

    protected static string $view =
        'filament.pages.email-settings';

    public ?array $data = [];

    /*
    |--------------------------------------------------------------------------
    | Access
    |--------------------------------------------------------------------------
    */

    public static function canAccess(): bool
    {
        return auth()->user()?->role
            === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $settings = EmailSetting::current();

        $this->form->fill([
            'auto_schedule_devotions' =>
                $settings->auto_schedule_devotions,

            'default_devotion_send_time' =>
                $settings->default_devotion_send_time,

            'default_recipient_scope' =>
                $settings->default_recipient_scope,

            'email_subscriber_group_id' =>
                $settings->email_subscriber_group_id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(
                    'Utumaji wa Tafakari kwa Barua Pepe'
                )
                    ->description(
                        'Weka jinsi tafakari mpya zitakavyotengenezewa na kupangiwa kampeni za barua pepe.'
                    )
                    ->schema([
                        Toggle::make(
                            'auto_schedule_devotions'
                        )
                            ->label(
                                'Panga Tafakari Kiotomatiki'
                            )
                            ->helperText(
                                'Ikiwashwa, kampeni ya barua pepe itatengenezwa na kupangwa kiotomatiki baada ya kuhifadhi tafakari.'
                            )
                            ->default(true)
                            ->live(),

                        TimePicker::make(
                            'default_devotion_send_time'
                        )
                            ->label(
                                'Muda wa Kawaida wa Kutuma'
                            )
                            ->seconds(false)
                            ->native(false)
                            ->required()
                            ->default('06:00')
                            ->helperText(
                                'Muda huu utajazwa kiotomatiki unapounda tafakari mpya. Unaweza kuubadilisha kwenye tafakari husika.'
                            ),

                        Select::make(
                            'default_recipient_scope'
                        )
                            ->label(
                                'Wapokeaji wa Kawaida'
                            )
                            ->options([
                                EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED =>
                                    'Wasajili Wote',

                                EmailCampaign::RECIPIENT_SCOPE_GROUP =>
                                    'Kundi la Wasajili',
                            ])
                            ->default(
                                EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED
                            )
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(
                                'Chagua wapokeaji ambao tafakari mpya zitatumwa kwao kwa kawaida.'
                            ),

                        Select::make(
                            'email_subscriber_group_id'
                        )
                            ->label(
                                'Kundi la Wasajili'
                            )
                            ->options(
                                fn (): array =>
                                    EmailSubscriberGroup::query()
                                        ->orderBy('name')
                                        ->pluck(
                                            'name',
                                            'id'
                                        )
                                        ->all()
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get(
                                        'default_recipient_scope'
                                    )
                                    === EmailCampaign::RECIPIENT_SCOPE_GROUP
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get(
                                        'default_recipient_scope'
                                    )
                                    === EmailCampaign::RECIPIENT_SCOPE_GROUP
                            )
                            ->helperText(
                                'Kundi hili litatumika kama wapokeaji wa tafakari zinazopangwa kiotomatiki.'
                            ),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    public function save(): void
    {
        $data = $this->form->getState();

        /*
        |--------------------------------------------------------------------------
        | Clear irrelevant group selection
        |--------------------------------------------------------------------------
        */

        if (
            $data['default_recipient_scope']
            !== EmailCampaign::RECIPIENT_SCOPE_GROUP
        ) {
            $data['email_subscriber_group_id'] =
                null;
        }

        $settings = EmailSetting::current();

        $settings->update([
            'auto_schedule_devotions' =>
                (bool) $data[
                    'auto_schedule_devotions'
                ],

            'default_devotion_send_time' =>
                $data[
                    'default_devotion_send_time'
                ],

            'default_recipient_scope' =>
                $data[
                    'default_recipient_scope'
                ],

            'email_subscriber_group_id' =>
                $data[
                    'email_subscriber_group_id'
                ]
                ?? null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Refresh form from saved settings
        |--------------------------------------------------------------------------
        */

        $settings->refresh();

        $this->form->fill([
            'auto_schedule_devotions' =>
                $settings
                    ->auto_schedule_devotions,

            'default_devotion_send_time' =>
                $settings
                    ->default_devotion_send_time,

            'default_recipient_scope' =>
                $settings
                    ->default_recipient_scope,

            'email_subscriber_group_id' =>
                $settings
                    ->email_subscriber_group_id,
        ]);

        Notification::make()
            ->title(
                'Mipangilio imehifadhiwa'
            )
            ->body(
                'Mipangilio ya utumaji wa tafakari kwa barua pepe imesasishwa.'
            )
            ->success()
            ->send();
    }
}