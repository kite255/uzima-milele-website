<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailCampaignResource\Pages;
use App\Filament\Resources\EmailCampaignResource\RelationManagers\RecipientsRelationManager;
use App\Mail\CustomCampaignMail;
use App\Mail\DevotionCampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use App\Services\Email\CampaignCloneService;
use App\Services\Email\CampaignExportService;
use App\Services\Email\CampaignSendingService;
use App\Services\EmailCampaignService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailCampaignResource extends Resource
{
    protected static ?string $model = EmailCampaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Kampeni za Barua Pepe';

    protected static ?string $modelLabel = 'Kampeni';

    protected static ?string $pluralModelLabel = 'Kampeni za Barua Pepe';

    protected static ?string $navigationGroup = 'Barua Pepe';

    protected static ?int $navigationSort = 2;

    /*
    |--------------------------------------------------------------------------
    | FORM
    |--------------------------------------------------------------------------
    */

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                /*
                |--------------------------------------------------------------------------
                | CAMPAIGN DETAILS
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Taarifa za Kampeni')
                    ->description(
                        'Tengeneza kampeni ya tafakari au ujumbe maalum.'
                    )
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Jina la Kampeni')
                            ->placeholder('Mfano: Tafakari ya Asubuhi')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->label('Aina ya Kampeni')
                            ->options([
                                EmailCampaign::TYPE_DEVOTION =>
                                    'Tafakari',

                                EmailCampaign::TYPE_CUSTOM =>
                                    'Ujumbe Maalum',
                            ])
                            ->default(
                                EmailCampaign::TYPE_DEVOTION
                            )
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('devotion_id')
                            ->label('Chagua Tafakari')
                            ->relationship(
                                name: 'devotion',
                                titleAttribute: 'title'
                            )
                            ->searchable()
                            ->preload()
                            ->required(
                                fn (Get $get): bool =>
                                    $get('type')
                                    === EmailCampaign::TYPE_DEVOTION
                            )
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('type')
                                    === EmailCampaign::TYPE_DEVOTION
                            ),

                        Forms\Components\TextInput::make('subject')
                            ->label('Kichwa cha Barua Pepe')
                            ->placeholder(
                                'Mfano: Tafakari ya Leo - Uzima Milele'
                            )
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->label('Ujumbe')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                                'link',
                                'undo',
                                'redo',
                            ])
                            ->required(
                                fn (Get $get): bool =>
                                    $get('type')
                                    === EmailCampaign::TYPE_CUSTOM
                            )
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('type')
                                    === EmailCampaign::TYPE_CUSTOM
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | AUDIENCE
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Wapokeaji')
                    ->description(
                        'Chagua wasajili watakaopokea kampeni hii.'
                    )
                    ->schema([
                        Forms\Components\Select::make('recipient_scope')
                            ->label('Watakaopokea')
                            ->options([
                                EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED =>
                                    'Wasajili Wote',

                                EmailCampaign::RECIPIENT_SCOPE_SELECTED =>
                                    'Wasajili Waliochaguliwa',

                                EmailCampaign::RECIPIENT_SCOPE_GROUP =>
                                    'Kundi la Wasajili',
                            ])
                            ->default(
                                EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED
                            )
                            ->required()
                            ->native(false)
                            ->live()
                            ->disabled(
                                fn (?EmailCampaign $record): bool =>
                                    $record !== null
                                    && ! $record->isDraft()
                            )
                            ->helperText(
                                'Chagua kama kampeni itatumwa kwa wasajili wote, waliochaguliwa, au kundi lililohifadhiwa.'
                            ),

                        /*
                        |--------------------------------------------------------------------------
                        | SELECTED SUBSCRIBERS
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Select::make(
                            'targetSubscribers'
                        )
                            ->label('Chagua Wasajili')
                            ->relationship(
                                name: 'targetSubscribers',
                                titleAttribute: 'email',
                                modifyQueryUsing:
                                    fn (Builder $query): Builder =>
                                        $query
                                            ->subscribed()
                                            ->orderBy('email')
                            )
                            ->getOptionLabelFromRecordUsing(
                                function (
                                    EmailSubscriber $record
                                ): string {
                                    $name = trim(
                                        $record->name
                                        ?: (
                                            ($record->first_name ?? '')
                                            . ' '
                                            . ($record->last_name ?? '')
                                        )
                                    );

                                    if ($name !== '') {
                                        return $name
                                            . ' — '
                                            . $record->email;
                                    }

                                    return $record->email;
                                }
                            )
                            ->multiple()
                            ->searchable([
                                'name',
                                'first_name',
                                'last_name',
                                'email',
                            ])
                            ->preload()
                            ->native(false)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('recipient_scope')
                                    === EmailCampaign::RECIPIENT_SCOPE_SELECTED
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get('recipient_scope')
                                    === EmailCampaign::RECIPIENT_SCOPE_SELECTED
                            )
                            ->disabled(
                                fn (?EmailCampaign $record): bool =>
                                    $record !== null
                                    && ! $record->isDraft()
                            )
                            ->helperText(
                                'Tafuta kwa jina au barua pepe. Unaweza kuchagua zaidi ya msajili mmoja.'
                            )
                            ->columnSpanFull(),

                        /*
                        |--------------------------------------------------------------------------
                        | SUBSCRIBER GROUP
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Select::make(
                            'email_subscriber_group_id'
                        )
                            ->label('Chagua Kundi')
                            ->relationship(
                                name: 'subscriberGroup',
                                titleAttribute: 'name'
                            )
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->visible(
                                fn (Get $get): bool =>
                                    $get('recipient_scope')
                                    === EmailCampaign::RECIPIENT_SCOPE_GROUP
                            )
                            ->required(
                                fn (Get $get): bool =>
                                    $get('recipient_scope')
                                    === EmailCampaign::RECIPIENT_SCOPE_GROUP
                            )
                            ->disabled(
                                fn (?EmailCampaign $record): bool =>
                                    $record !== null
                                    && ! $record->isDraft()
                            )
                            ->helperText(
                                'Chagua kundi la wasajili ambalo litapokea kampeni hii.'
                            )
                            ->columnSpanFull(),

                        /*
                        |--------------------------------------------------------------------------
                        | AUDIENCE SUMMARY
                        |--------------------------------------------------------------------------
                        */

                        Forms\Components\Placeholder::make(
                            'recipient_information'
                        )
                            ->label('Muhtasari wa Wapokeaji')
                            ->content(
                                function (Get $get): string {
                                    if (
                                        $get('recipient_scope')
                                        === EmailCampaign::RECIPIENT_SCOPE_SELECTED
                                    ) {
                                        $selected =
                                            $get('targetSubscribers')
                                            ?? [];

                                        $count = is_array($selected)
                                            ? count($selected)
                                            : 0;

                                        if ($count === 0) {
                                            return 'Hakuna msajili aliyechaguliwa bado.';
                                        }

                                        return number_format($count)
                                            . ' wasajili wamechaguliwa kupokea kampeni hii.';
                                    }

                                    if (
                                        $get('recipient_scope')
                                        === EmailCampaign::RECIPIENT_SCOPE_GROUP
                                    ) {
                                        $groupId =
                                            $get(
                                                'email_subscriber_group_id'
                                            );

                                        if (blank($groupId)) {
                                            return 'Hakuna kundi lililochaguliwa bado.';
                                        }

                                        $group =
                                            \App\Models\EmailSubscriberGroup::query()
                                                ->withCount([
                                                    'subscribers as active_subscribers_count' =>
                                                        fn (Builder $query) =>
                                                            $query->where(
                                                                'email_subscribers.status',
                                                                'subscribed'
                                                            ),
                                                ])
                                                ->find($groupId);

                                        if (! $group) {
                                            return 'Kundi lililochaguliwa halipatikani.';
                                        }

                                        return number_format(
                                            $group->active_subscribers_count
                                        )
                                            . ' wasajili hai katika kundi “'
                                            . $group->name
                                            . '” watapokea kampeni hii.';
                                    }

                                    $count = app(
                                        EmailCampaignService::class
                                    )->activeSubscriberCount();

                                    return number_format($count)
                                        . ' wasajili hai watapokea kampeni hii.';
                                }
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                /*
                |--------------------------------------------------------------------------
                | SCHEDULE INFORMATION
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Ratiba ya Kampeni')
                    ->description(
                        'Kampeni inaweza kutumwa sasa au kupangwa kutumwa baadaye baada ya kuhifadhi rasimu.'
                    )
                    ->schema([
                        Forms\Components\Placeholder::make(
                            'schedule_information'
                        )
                            ->label('Ratiba')
                            ->content(
                                function (
                                    ?EmailCampaign $record
                                ): string {
                                    if (! $record) {
                                        return 'Hifadhi kampeni kwanza, kisha chagua Tuma Sasa au Panga Kutumwa.';
                                    }

                                    if (
                                        $record->status
                                        === EmailCampaign::STATUS_SCHEDULED
                                        && $record->scheduled_at
                                    ) {
                                        return 'Imepangwa kutumwa: '
                                            . $record
                                                ->scheduled_at
                                                ->format(
                                                    'd M Y, H:i'
                                                );
                                    }

                                    return 'Hakuna ratiba ya kutuma iliyowekwa.';
                                }
                            ),
                    ])
                    ->visible(
                        fn (?EmailCampaign $record): bool =>
                            $record !== null
                    ),

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */

                Forms\Components\Section::make('Hali ya Kampeni')
                    ->schema([
                        Forms\Components\Placeholder::make(
                            'status_information'
                        )
                            ->label('Hali')
                            ->content(
                                function (
                                    ?EmailCampaign $record
                                ): string {
                                    if (! $record) {
                                        return 'Rasimu';
                                    }

                                    return match ($record->status) {
                                        EmailCampaign::STATUS_DRAFT =>
                                            'Rasimu',

                                        EmailCampaign::STATUS_SCHEDULED =>
                                            'Imepangwa',

                                        EmailCampaign::STATUS_QUEUED =>
                                            'Kwenye Foleni',

                                        EmailCampaign::STATUS_SENDING =>
                                            'Inatumwa',

                                        EmailCampaign::STATUS_SENT =>
                                            'Imetumwa',

                                        EmailCampaign::STATUS_FAILED =>
                                            'Imeshindwa',

                                        default =>
                                            ucfirst(
                                                $record->status
                                            ),
                                    };
                                }
                            ),
                    ])
                    ->visible(
                        fn (?EmailCampaign $record): bool =>
                            $record !== null
                    ),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CAMPAIGN REPORT
    |--------------------------------------------------------------------------
    */

    public static function infolist(
        Infolist $infolist
    ): Infolist {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(
                    'Muhtasari wa Kampeni'
                )
                    ->description(
                        'Taarifa kuu za kampeni na hali yake ya sasa.'
                    )
                    ->icon('heroicon-o-megaphone')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Jina la Kampeni')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('type')
                            ->label('Aina')
                            ->badge()
                            ->formatStateUsing(
                                fn (string $state): string =>
                                    match ($state) {
                                        EmailCampaign::TYPE_DEVOTION =>
                                            'Tafakari',

                                        EmailCampaign::TYPE_CUSTOM =>
                                            'Ujumbe Maalum',

                                        default =>
                                            $state,
                                    }
                            )
                            ->color(
                                fn (string $state): string =>
                                    match ($state) {
                                        EmailCampaign::TYPE_DEVOTION =>
                                            'info',

                                        EmailCampaign::TYPE_CUSTOM =>
                                            'warning',

                                        default =>
                                            'gray',
                                    }
                            ),

                        Infolists\Components\TextEntry::make(
                            'recipient_scope'
                        )
                            ->label('Wapokeaji')
                            ->badge()
                            ->formatStateUsing(
                                fn (?string $state): string =>
                                    match ($state) {
                                        EmailCampaign::RECIPIENT_SCOPE_SELECTED =>
                                            'Waliochaguliwa',

                                        EmailCampaign::RECIPIENT_SCOPE_GROUP =>
                                            'Kundi la Wasajili',

                                        EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED =>
                                            'Wasajili Wote',

                                        default =>
                                            'Wasajili Wote',
                                    }
                            )
                            ->color(
                                fn (?string $state): string =>
                                    match ($state) {
                                        EmailCampaign::RECIPIENT_SCOPE_SELECTED =>
                                            'warning',

                                        EmailCampaign::RECIPIENT_SCOPE_GROUP =>
                                            'success',

                                        default =>
                                            'info',
                                    }
                            ),

                        Infolists\Components\TextEntry::make(
                            'subscriberGroup.name'
                        )
                            ->label('Kundi')
                            ->placeholder('—')
                            ->visible(
                                fn (
                                    EmailCampaign $record
                                ): bool =>
                                    $record
                                        ->sendsToSubscriberGroup()
                            ),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Hali')
                            ->badge()
                            ->formatStateUsing(
                                fn (string $state): string =>
                                    match ($state) {
                                        EmailCampaign::STATUS_DRAFT =>
                                            'Rasimu',

                                        EmailCampaign::STATUS_SCHEDULED =>
                                            'Imepangwa',

                                        EmailCampaign::STATUS_QUEUED =>
                                            'Kwenye Foleni',

                                        EmailCampaign::STATUS_SENDING =>
                                            'Inatumwa',

                                        EmailCampaign::STATUS_SENT =>
                                            'Imetumwa',

                                        EmailCampaign::STATUS_FAILED =>
                                            'Imeshindwa',

                                        default =>
                                            $state,
                                    }
                            )
                            ->color(
                                fn (string $state): string =>
                                    match ($state) {
                                        EmailCampaign::STATUS_DRAFT =>
                                            'gray',

                                        EmailCampaign::STATUS_SCHEDULED =>
                                            'warning',

                                        EmailCampaign::STATUS_QUEUED =>
                                            'warning',

                                        EmailCampaign::STATUS_SENDING =>
                                            'info',

                                        EmailCampaign::STATUS_SENT =>
                                            'success',

                                        EmailCampaign::STATUS_FAILED =>
                                            'danger',

                                        default =>
                                            'gray',
                                    }
                            ),

                        Infolists\Components\TextEntry::make('subject')
                            ->label('Kichwa cha Barua Pepe')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make(
                            'creator.name'
                        )
                            ->label('Aliyetengeneza')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make(
                            'created_at'
                        )
                            ->label('Tarehe ya Kutengenezwa')
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make(
                            'scheduled_at'
                        )
                            ->label('Imepangwa Kutumwa')
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 3,
                    ]),

                Infolists\Components\Section::make(
                    'Matokeo ya Uwasilishaji'
                )
                    ->description(
                        'Muhtasari wa uwasilishaji na ufunguaji wa barua pepe.'
                    )
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Infolists\Components\TextEntry::make(
                            'total_recipients'
                        )
                            ->label('Wapokeaji')
                            ->numeric()
                            ->weight('bold')
                            ->icon('heroicon-o-users'),

                        Infolists\Components\TextEntry::make(
                            'sent_count'
                        )
                            ->label('Zimetumwa')
                            ->numeric()
                            ->weight('bold')
                            ->color('success')
                            ->icon(
                                'heroicon-o-check-circle'
                            ),

                        Infolists\Components\TextEntry::make(
                            'failed_count'
                        )
                            ->label('Zimeshindwa')
                            ->numeric()
                            ->weight('bold')
                            ->icon(
                                'heroicon-o-x-circle'
                            )
                            ->color(
                                fn (
                                    EmailCampaign $record
                                ): string =>
                                    $record->failed_count > 0
                                        ? 'danger'
                                        : 'gray'
                            ),

                        /*
                        |--------------------------------------------------------------------------
                        | OPEN TRACKING
                        |--------------------------------------------------------------------------
                        */

                        Infolists\Components\TextEntry::make(
                            'opened_recipients'
                        )
                            ->label('Zimefunguliwa')
                            ->state(
                                fn (
                                    EmailCampaign $record
                                ): int =>
                                    $record
                                        ->openedRecipientsCount()
                            )
                            ->numeric()
                            ->weight('bold')
                            ->color('success')
                            ->icon(
                                'heroicon-o-envelope-open'
                            ),

                        Infolists\Components\TextEntry::make(
                            'unopened_recipients'
                        )
                            ->label('Hazijafunguliwa')
                            ->state(
                                fn (
                                    EmailCampaign $record
                                ): int =>
                                    $record
                                        ->unopenedRecipientsCount()
                            )
                            ->numeric()
                            ->weight('bold')
                            ->color('gray')
                            ->icon(
                                'heroicon-o-envelope'
                            ),

                        Infolists\Components\TextEntry::make(
                            'open_rate'
                        )
                            ->label(
                                'Kiwango cha Kufunguliwa'
                            )
                            ->state(
                                fn (
                                    EmailCampaign $record
                                ): string =>
                                    number_format(
                                        $record->openRate(),
                                        1
                                    ) . '%'
                            )
                            ->weight('bold')
                            ->icon(
                                'heroicon-o-chart-bar-square'
                            )
                            ->color(
                                function (
                                    EmailCampaign $record
                                ): string {
                                    $percentage =
                                        $record->openRate();

                                    if (
                                        $percentage >= 50
                                    ) {
                                        return 'success';
                                    }

                                    if (
                                        $percentage >= 25
                                    ) {
                                        return 'warning';
                                    }

                                    return $percentage > 0
                                        ? 'danger'
                                        : 'gray';
                                }
                            ),

                        /*
                        |--------------------------------------------------------------------------
                        | DELIVERY RATE
                        |--------------------------------------------------------------------------
                        */

                        Infolists\Components\TextEntry::make(
                            'delivery_rate'
                        )
                            ->label('Kiwango cha Uwasilishaji')
                            ->state(
                                function (
                                    EmailCampaign $record
                                ): string {
                                    if (
                                        $record->total_recipients
                                        <= 0
                                    ) {
                                        return '0%';
                                    }

                                    $percentage = (
                                        $record->sent_count
                                        / $record->total_recipients
                                    ) * 100;

                                    return number_format(
                                        $percentage,
                                        1
                                    ) . '%';
                                }
                            )
                            ->weight('bold')
                            ->color(
                                function (
                                    EmailCampaign $record
                                ): string {
                                    if (
                                        $record->total_recipients
                                        <= 0
                                    ) {
                                        return 'gray';
                                    }

                                    $percentage = (
                                        $record->sent_count
                                        / $record->total_recipients
                                    ) * 100;

                                    if ($percentage >= 90) {
                                        return 'success';
                                    }

                                    if ($percentage >= 50) {
                                        return 'warning';
                                    }

                                    return 'danger';
                                }
                            )
                            ->icon(
                                'heroicon-o-chart-pie'
                            ),

                        Infolists\Components\TextEntry::make(
                            'queued_at'
                        )
                            ->label('Iliwekwa Foleni')
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),

                        Infolists\Components\TextEntry::make(
                            'sent_at'
                        )
                            ->label(
                                'Uwasilishaji Ulikamilika'
                            )
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),
                    ])
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 3,
                    ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    public static function table(
        Table $table
    ): Table {
        return $table
            ->defaultSort(
                'created_at',
                'desc'
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Kampeni')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Aina')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string =>
                            match ($state) {
                                EmailCampaign::TYPE_DEVOTION =>
                                    'Tafakari',

                                EmailCampaign::TYPE_CUSTOM =>
                                    'Ujumbe Maalum',

                                default =>
                                    $state,
                            }
                    )
                    ->color(
                        fn (string $state): string =>
                            match ($state) {
                                EmailCampaign::TYPE_DEVOTION =>
                                    'info',

                                EmailCampaign::TYPE_CUSTOM =>
                                    'warning',

                                default =>
                                    'gray',
                            }
                    ),

                Tables\Columns\TextColumn::make(
                    'recipient_scope'
                )
                    ->label('Wapokeaji')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string =>
                            match ($state) {
                                EmailCampaign::RECIPIENT_SCOPE_SELECTED =>
                                    'Waliochaguliwa',

                                EmailCampaign::RECIPIENT_SCOPE_GROUP =>
                                    'Kundi',

                                EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED =>
                                    'Wote',

                                default =>
                                    'Wote',
                            }
                    )
                    ->color(
                        fn (?string $state): string =>
                            match ($state) {
                                EmailCampaign::RECIPIENT_SCOPE_SELECTED =>
                                    'warning',

                                EmailCampaign::RECIPIENT_SCOPE_GROUP =>
                                    'success',

                                default =>
                                    'info',
                            }
                    ),

                Tables\Columns\TextColumn::make(
                    'subscriberGroup.name'
                )
                    ->label('Kundi')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Kichwa')
                    ->searchable()
                    ->limit(45),

                Tables\Columns\TextColumn::make('status')
                    ->label('Hali')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string =>
                            match ($state) {
                                EmailCampaign::STATUS_DRAFT =>
                                    'Rasimu',

                                EmailCampaign::STATUS_SCHEDULED =>
                                    'Imepangwa',

                                EmailCampaign::STATUS_QUEUED =>
                                    'Kwenye Foleni',

                                EmailCampaign::STATUS_SENDING =>
                                    'Inatumwa',

                                EmailCampaign::STATUS_SENT =>
                                    'Imetumwa',

                                EmailCampaign::STATUS_FAILED =>
                                    'Imeshindwa',

                                default =>
                                    $state,
                            }
                    )
                    ->color(
                        fn (string $state): string =>
                            match ($state) {
                                EmailCampaign::STATUS_DRAFT =>
                                    'gray',

                                EmailCampaign::STATUS_SCHEDULED =>
                                    'warning',

                                EmailCampaign::STATUS_QUEUED =>
                                    'warning',

                                EmailCampaign::STATUS_SENDING =>
                                    'info',

                                EmailCampaign::STATUS_SENT =>
                                    'success',

                                EmailCampaign::STATUS_FAILED =>
                                    'danger',

                                default =>
                                    'gray',
                            }
                    ),

                Tables\Columns\TextColumn::make(
                    'scheduled_at'
                )
                    ->label('Ratiba')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'total_recipients'
                )
                    ->label('Wapokeaji')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'sent_count'
                )
                    ->label('Zimetumwa')
                    ->numeric()
                    ->color('success')
                    ->sortable(),

                /*
                |--------------------------------------------------------------------------
                | OPEN TRACKING COLUMNS
                |--------------------------------------------------------------------------
                */

                Tables\Columns\TextColumn::make(
                    'opened_count'
                )
                    ->label('Zimefunguliwa')
                    ->state(
                        fn (
                            EmailCampaign $record
                        ): int =>
                            $record
                                ->openedRecipientsCount()
                    )
                    ->numeric()
                    ->color('success')
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'open_rate'
                )
                    ->label('Open Rate')
                    ->state(
                        fn (
                            EmailCampaign $record
                        ): string =>
                            number_format(
                                $record->openRate(),
                                1
                            ) . '%'
                    )
                    ->color(
                        function (
                            EmailCampaign $record
                        ): string {
                            $percentage =
                                $record->openRate();

                            if (
                                $percentage >= 50
                            ) {
                                return 'success';
                            }

                            if (
                                $percentage >= 25
                            ) {
                                return 'warning';
                            }

                            return $percentage > 0
                                ? 'danger'
                                : 'gray';
                        }
                    )
                    ->toggleable(),

                Tables\Columns\TextColumn::make(
                    'failed_count'
                )
                    ->label('Zimeshindwa')
                    ->numeric()
                    ->color(
                        fn (
                            EmailCampaign $record
                        ): string =>
                            $record->failed_count > 0
                                ? 'danger'
                                : 'gray'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make(
                    'created_at'
                )
                    ->label('Imeundwa')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make(
                    'status'
                )
                    ->label('Hali')
                    ->options([
                        EmailCampaign::STATUS_DRAFT =>
                            'Rasimu',

                        EmailCampaign::STATUS_SCHEDULED =>
                            'Imepangwa',

                        EmailCampaign::STATUS_QUEUED =>
                            'Kwenye Foleni',

                        EmailCampaign::STATUS_SENDING =>
                            'Inatumwa',

                        EmailCampaign::STATUS_SENT =>
                            'Imetumwa',

                        EmailCampaign::STATUS_FAILED =>
                            'Imeshindwa',
                    ]),

                Tables\Filters\SelectFilter::make(
                    'type'
                )
                    ->label('Aina')
                    ->options([
                        EmailCampaign::TYPE_DEVOTION =>
                            'Tafakari',

                        EmailCampaign::TYPE_CUSTOM =>
                            'Ujumbe Maalum',
                    ]),

                Tables\Filters\SelectFilter::make(
                    'recipient_scope'
                )
                    ->label('Wapokeaji')
                    ->options([
                        EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED =>
                            'Wasajili Wote',

                        EmailCampaign::RECIPIENT_SCOPE_SELECTED =>
                            'Waliochaguliwa',

                        EmailCampaign::RECIPIENT_SCOPE_GROUP =>
                            'Kundi la Wasajili',
                    ]),

                Tables\Filters\SelectFilter::make(
                    'email_subscriber_group_id'
                )
                    ->label('Kundi')
                    ->relationship(
                        'subscriberGroup',
                        'name'
                    ),
            ])
            ->actions([
                /*
                |--------------------------------------------------------------------------
                | VIEW REPORT
                |--------------------------------------------------------------------------
                */

                Tables\Actions\ViewAction::make()
                    ->label('Tazama Ripoti')
                    ->icon(
                        'heroicon-o-chart-bar'
                    )
                    ->color('info'),

                /*
                |--------------------------------------------------------------------------
                | EDIT
                |--------------------------------------------------------------------------
                */

                Tables\Actions\EditAction::make()
                    ->label('Hariri')
                    ->visible(
                        fn (
                            EmailCampaign $record
                        ): bool =>
                            in_array(
                                $record->status,
                                [
                                    EmailCampaign::STATUS_DRAFT,
                                    EmailCampaign::STATUS_SCHEDULED,
                                ],
                                true
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | PREVIEW
                |--------------------------------------------------------------------------
                */

                Tables\Actions\Action::make(
                    'preview'
                )
                    ->label('Hakiki')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(
                        fn (
                            EmailCampaign $record
                        ): string =>
                            'Hakiki: '
                            . $record->name
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(
                        'Funga'
                    )
                    ->modalWidth('3xl')
                    ->modalContent(
                        fn (
                            EmailCampaign $record
                        ) =>
                            view(
                                'filament.email-campaigns.preview',
                                [
                                    'campaign' =>
                                        $record
                                            ->load(
                                                'devotion'
                                            ),
                                ]
                            )
                    ),

                /*
                |--------------------------------------------------------------------------
                | TEST EMAIL
                |--------------------------------------------------------------------------
                */

                Tables\Actions\Action::make(
                    'test_email'
                )
                    ->label('Tuma Jaribio')
                    ->icon(
                        'heroicon-o-paper-airplane'
                    )
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make(
                            'email'
                        )
                            ->label(
                                'Barua Pepe ya Majaribio'
                            )
                            ->email()
                            ->required(),

                        Forms\Components\TextInput::make(
                            'name'
                        )
                            ->label(
                                'Jina la Mpokeaji'
                            )
                            ->default('Mjaribio')
                            ->maxLength(100),
                    ])
                    ->modalHeading(
                        'Tuma Barua Pepe ya Majaribio'
                    )
                    ->modalSubmitActionLabel(
                        'Tuma Jaribio'
                    )
                    ->action(
                        function (
                            EmailCampaign $record,
                            array $data
                        ): void {
                            try {
                                $record->load(
                                    'devotion'
                                );

                                $recipient =
                                    new EmailCampaignRecipient([
                                        'name' =>
                                            $data['name']
                                            ?: 'Mjaribio',

                                        'email' =>
                                            strtolower(
                                                trim(
                                                    $data['email']
                                                )
                                            ),

                                        'status' =>
                                            EmailCampaignRecipient::STATUS_PENDING,
                                    ]);

                                if (
                                    $record
                                        ->isDevotion()
                                ) {
                                    if (
                                        ! $record->devotion
                                    ) {
                                        Notification::make()
                                            ->title(
                                                'Tafakari haijachaguliwa'
                                            )
                                            ->danger()
                                            ->send();

                                        return;
                                    }

                                    Mail::to(
                                        $data['email']
                                    )
                                        ->send(
                                            new DevotionCampaignMail(
                                                campaign:
                                                    $record,

                                                recipient:
                                                    $recipient,
                                            )
                                        );
                                } elseif (
                                    $record
                                        ->isCustom()
                                ) {
                                    if (
                                        blank(
                                            $record->content
                                        )
                                    ) {
                                        Notification::make()
                                            ->title(
                                                'Ujumbe wa kampeni haujaandikwa'
                                            )
                                            ->danger()
                                            ->send();

                                        return;
                                    }

                                    Mail::to(
                                        $data['email']
                                    )
                                        ->send(
                                            new CustomCampaignMail(
                                                campaign:
                                                    $record,

                                                recipient:
                                                    $recipient,
                                            )
                                        );
                                }

                                Notification::make()
                                    ->title(
                                        'Barua pepe ya majaribio imetumwa'
                                    )
                                    ->body(
                                        'Jaribio limetumwa kwenda '
                                        . $data['email']
                                    )
                                    ->success()
                                    ->send();
                            } catch (
                                Throwable $exception
                            ) {
                                report(
                                    $exception
                                );

                                Notification::make()
                                    ->title(
                                        'Jaribio limeshindwa'
                                    )
                                    ->body(
                                        $exception
                                            ->getMessage()
                                    )
                                    ->danger()
                                    ->send();
                            }
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | SEND NOW
                |--------------------------------------------------------------------------
                */

                Tables\Actions\Action::make('send')
                    ->label('Tuma Sasa')
                    ->icon(
                        'heroicon-o-paper-airplane'
                    )
                    ->color('success')
                    ->visible(
                        fn (
                            EmailCampaign $record
                        ): bool =>
                            $record->status
                            === EmailCampaign::STATUS_DRAFT
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Tuma Kampeni Sasa'
                    )
                    ->modalDescription(
                        'Kampeni itawekwa kwenye foleni na kuanza kutumwa kwa wapokeaji wa kampeni hii.'
                    )
                    ->modalSubmitActionLabel(
                        'Ndiyo, Tuma Sasa'
                    )
                    ->action(
                        function (
                            EmailCampaign $record
                        ): void {
                            try {
                                $campaign = app(
                                    EmailCampaignService::class
                                )->queueCampaign(
                                    $record
                                );

                                Notification::make()
                                    ->title(
                                        'Kampeni imewekwa kwenye foleni'
                                    )
                                    ->body(
                                        number_format(
                                            $campaign
                                                ->total_recipients
                                        )
                                        . ' wapokeaji wameandaliwa.'
                                    )
                                    ->success()
                                    ->send();
                            } catch (
                                Throwable $exception
                            ) {
                                report(
                                    $exception
                                );

                                Notification::make()
                                    ->title(
                                        'Kampeni haikuweza kutumwa'
                                    )
                                    ->body(
                                        $exception
                                            ->getMessage()
                                    )
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | SCHEDULE
                |--------------------------------------------------------------------------
                */

                Tables\Actions\Action::make(
                    'schedule'
                )
                    ->label('Panga Kutumwa')
                    ->icon(
                        'heroicon-o-calendar-days'
                    )
                    ->color('warning')
                    ->visible(
                        fn (
                            EmailCampaign $record
                        ): bool =>
                            $record->status
                            === EmailCampaign::STATUS_DRAFT
                    )
                    ->form([
                        Forms\Components\DatePicker::make(
                            'scheduled_date'
                        )
                            ->label(
                                'Tarehe ya Kutuma'
                            )
                            ->placeholder(
                                'Chagua tarehe'
                            )
                            ->prefixIcon(
                                'heroicon-o-calendar-days'
                            )
                            ->displayFormat(
                                'd M Y'
                            )
                            ->format('Y-m-d')
                            ->native(false)
                            ->minDate(today())
                            ->default(today())
                            ->required(),

                        Forms\Components\TimePicker::make(
                            'scheduled_time'
                        )
                            ->label(
                                'Muda wa Kutuma'
                            )
                            ->prefixIcon(
                                'heroicon-o-clock'
                            )
                            ->seconds(false)
                            ->format('H:i')
                            ->displayFormat('H:i')
                            ->native(true)
                            ->required()
                            ->rules([
                                'date_format:H:i',
                            ])
                            ->helperText(
                                'Andika muda moja kwa moja au tumia time picker, mfano 06:17, 13:07 au 20:43.'
                            ),

                        Forms\Components\Placeholder::make(
                            'timezone_information'
                        )
                            ->label(
                                'Saa za Mfumo'
                            )
                            ->content(
                                'Ratiba itatumia muda wa mfumo: '
                                . config(
                                    'app.timezone',
                                    'UTC'
                                )
                            ),
                    ])
                    ->modalHeading(
                        'Panga Kampeni Kutumwa'
                    )
                    ->modalDescription(
                        'Chagua tarehe na muda ambao kampeni hii itaanza kutumwa.'
                    )
                    ->modalSubmitActionLabel(
                        'Hifadhi Ratiba'
                    )
                    ->modalWidth('lg')
                    ->action(
                        function (
                            EmailCampaign $record,
                            array $data
                        ): void {
                            try {
                                $scheduledAt =
                                    $data[
                                        'scheduled_date'
                                    ]
                                    . ' '
                                    . $data[
                                        'scheduled_time'
                                    ];

                                $campaign = app(
                                    EmailCampaignService::class
                                )->scheduleCampaign(
                                    $record,
                                    $scheduledAt
                                );

                                Notification::make()
                                    ->title(
                                        'Kampeni imepangwa'
                                    )
                                    ->body(
                                        'Itatumwa tarehe '
                                        . $campaign
                                            ->scheduled_at
                                            ->format(
                                                'd M Y, H:i'
                                            )
                                        . '.'
                                    )
                                    ->success()
                                    ->send();
                            } catch (
                                Throwable $exception
                            ) {
                                report(
                                    $exception
                                );

                                Notification::make()
                                    ->title(
                                        'Kampeni haikuweza kupangwa'
                                    )
                                    ->body(
                                        $exception
                                            ->getMessage()
                                    )
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        }
                    ),

                /*
                |--------------------------------------------------------------------------
                | CANCEL SCHEDULE
                |--------------------------------------------------------------------------
                */

                Tables\Actions\Action::make(
                    'cancel_schedule'
                )
                    ->label('Ghairi Ratiba')
                    ->icon(
                        'heroicon-o-x-mark'
                    )
                    ->color('gray')
                    ->visible(
                        fn (
                            EmailCampaign $record
                        ): bool =>
                            $record->status
                            === EmailCampaign::STATUS_SCHEDULED
                    )
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Ghairi Ratiba'
                    )
                    ->modalDescription(
                        'Kampeni itarudishwa kuwa rasimu na haitatumwa kwa muda uliopangwa.'
                    )
                    ->modalSubmitActionLabel(
                        'Ndiyo, Ghairi Ratiba'
                    )
                    ->action(
                        function (
                            EmailCampaign $record
                        ): void {
                            try {
                                app(
                                    EmailCampaignService::class
                                )->cancelScheduledCampaign(
                                    $record
                                );

                                Notification::make()
                                    ->title(
                                        'Ratiba imeghairiwa'
                                    )
                                    ->body(
                                        'Kampeni imerudishwa kuwa rasimu.'
                                    )
                                    ->success()
                                    ->send();
                            } catch (
                                Throwable $exception
                            ) {
                                report(
                                    $exception
                                );

                                Notification::make()
                                    ->title(
                                        'Ratiba haikuweza kughairiwa'
                                    )
                                    ->body(
                                        $exception
                                            ->getMessage()
                                    )
                                    ->danger()
                                    ->send();
                            }
                        }
                    ),

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
                /*
                |--------------------------------------------------------------------------
                | DELETE
                |--------------------------------------------------------------------------
                */

                Tables\Actions\DeleteAction::make()
                    ->label('Futa')
                    ->visible(
                        fn (
                            EmailCampaign $record
                        ): bool =>
                            $record->status
                            === EmailCampaign::STATUS_DRAFT
                    ),
            ])
            ->emptyStateHeading(
                'Hakuna kampeni za barua pepe'
            )
            ->emptyStateDescription(
                'Tengeneza kampeni yako ya kwanza ya barua pepe.'
            )
            ->emptyStateIcon(
                'heroicon-o-megaphone'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public static function getRelations(): array
    {
        return [
            RecipientsRelationManager::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PAGES
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [
            'index' =>
                Pages\ListEmailCampaigns::route(
                    '/'
                ),

            'create' =>
                Pages\CreateEmailCampaign::route(
                    '/create'
                ),

            'view' =>
                Pages\ViewEmailCampaign::route(
                    '/{record}'
                ),

            'edit' =>
                Pages\EditEmailCampaign::route(
                    '/{record}/edit'
                ),
        ];
    }
}
