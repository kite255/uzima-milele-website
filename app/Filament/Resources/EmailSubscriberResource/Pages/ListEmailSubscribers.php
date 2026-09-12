<?php

namespace App\Filament\Resources\EmailSubscriberResource\Pages;

use App\Exports\EmailSubscribersExport;
use App\Filament\Resources\EmailSubscriberResource;
use App\Imports\EmailSubscribersImport;
use Filament\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Throwable;

class ListEmailSubscribers extends ListRecords
{
    protected static string $resource = EmailSubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | IMPORT SUBSCRIBERS
            |--------------------------------------------------------------------------
            */
            Actions\Action::make('import')
                ->label('Ingiza Wasajili')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Ingiza Wasajili')
                ->modalDescription(
                    'Pakia faili la CSV, XLSX au XLS lenye taarifa za wasajili.'
                )
                ->modalWidth('2xl')
                ->form([

                    /*
                    |--------------------------------------------------------------------------
                    | DOWNLOAD TEMPLATE
                    |--------------------------------------------------------------------------
                    */
                    Placeholder::make('download_template')
                        ->label('Template ya Kuingiza Wasajili')
                        ->content(function (): HtmlString {
                            $templateUrl = asset(
                                'templates/uzima-milele-subscriber-import-template.xlsx'
                            );

                            return new HtmlString(
                                '
                                <div
                                    style="
                                        padding: 16px;
                                        border: 1px solid #e5e7eb;
                                        border-radius: 12px;
                                        background: #f9fafb;
                                    "
                                >
                                    <div
                                        style="
                                            font-weight: 700;
                                            margin-bottom: 6px;
                                            color: #111827;
                                        "
                                    >
                                        Pakua mfano wa faili kabla ya kuingiza wasajili.
                                    </div>

                                    <div
                                        style="
                                            font-size: 13px;
                                            color: #6b7280;
                                            margin-bottom: 14px;
                                        "
                                    >
                                        Template ina safu zinazopendekezwa tayari:
                                        name, email na phone.
                                    </div>

                                    <a
                                        href="' . e($templateUrl) . '"
                                        download
                                        style="
                                            display: inline-block;
                                            padding: 10px 16px;
                                            border-radius: 8px;
                                            background: #0083CB;
                                            color: #ffffff;
                                            text-decoration: none;
                                            font-weight: 700;
                                            font-size: 14px;
                                        "
                                    >
                                        Pakua Template ya Excel
                                    </a>
                                </div>
                                '
                            );
                        }),

                    /*
                    |--------------------------------------------------------------------------
                    | REQUIRED COLUMNS
                    |--------------------------------------------------------------------------
                    */
                    Placeholder::make('columns_information')
                        ->label('Safu za Faili')
                        ->content(
                            new HtmlString(
                                '
                                <div
                                    style="
                                        padding: 16px;
                                        border: 1px solid #e5e7eb;
                                        border-radius: 12px;
                                        background: #ffffff;
                                        font-size: 14px;
                                        line-height: 1.7;
                                    "
                                >
                                    <div style="margin-bottom: 12px;">
                                        <strong>Safu ya lazima:</strong>
                                        <br>
                                        <code>email</code>
                                    </div>

                                    <div style="margin-bottom: 12px;">
                                        <strong>Safu inayopendekezwa:</strong>
                                        <br>
                                        <code>name</code>
                                    </div>

                                    <div style="margin-bottom: 12px;">
                                        <strong>Safu ya hiari:</strong>
                                        <br>
                                        <code>phone</code>
                                    </div>

                                    <div style="margin-bottom: 12px;">
                                        <strong>Safu mbadala za jina:</strong>
                                        <br>
                                        <code>full_name</code>,
                                        <code>first_name</code>,
                                        <code>last_name</code>
                                    </div>

                                    <div
                                        style="
                                            padding-top: 12px;
                                            border-top: 1px solid #e5e7eb;
                                        "
                                    >
                                        <strong>Mfano:</strong>

                                        <div
                                            style="
                                                margin-top: 8px;
                                                padding: 10px;
                                                border-radius: 8px;
                                                background: #f3f4f6;
                                                font-family: monospace;
                                                overflow-x: auto;
                                            "
                                        >
                                            name,email,phone
                                            <br>
                                            Kitenken Lucas,kitenken@gmail.com,0768461644
                                        </div>
                                    </div>
                                </div>
                                '
                            )
                        ),

                    /*
                    |--------------------------------------------------------------------------
                    | FILE UPLOAD
                    |--------------------------------------------------------------------------
                    */
                    FileUpload::make('file')
                        ->label('Chagua Faili')
                        ->helperText(
                            'Aina zinazokubalika: CSV, XLSX au XLS.'
                        )
                        ->disk('local')
                        ->directory('subscriber-imports')
                        ->acceptedFileTypes([
                            'text/csv',
                            'application/csv',
                            'text/plain',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->maxSize(10240)
                        ->required(),

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE EXISTING
                    |--------------------------------------------------------------------------
                    */
                    Checkbox::make('update_existing')
                        ->label(
                            'Sasisha wasajili wenye barua pepe ambazo tayari zipo'
                        )
                        ->helperText(
                            'Ikiwa haijachaguliwa, wasajili wenye barua pepe ambazo tayari zipo watarukwa.'
                        ),

                    /*
                    |--------------------------------------------------------------------------
                    | DEFAULT STATUS
                    |--------------------------------------------------------------------------
                    */
                    Select::make('default_status')
                        ->label('Hali ya wasajili wanaoingizwa')
                        ->options([
                            'subscribed' => 'Wamejiandikisha',
                            'unsubscribed' => 'Wamejiondoa',
                        ])
                        ->default('subscribed')
                        ->required(),
                ])
                ->modalSubmitActionLabel('Ingiza Wasajili')
                ->modalCancelActionLabel('Ghairi')
                ->action(function (array $data): void {

                    $storedPath = $data['file'];

                    $absolutePath = Storage::disk('local')
                        ->path($storedPath);

                    $importer = new EmailSubscribersImport(
                        updateExisting:
                            (bool) ($data['update_existing'] ?? false),

                        defaultStatus:
                            $data['default_status'] ?? 'subscribed',
                    );

                    try {

                        Excel::import(
                            $importer,
                            $absolutePath
                        );

                        Notification::make()
                            ->title('Uingizaji umekamilika')
                            ->body(
                                "Wapya: {$importer->imported} | " .
                                "Wamesasishwa: {$importer->updated} | " .
                                "Waliorukwa: {$importer->skipped} | " .
                                "Walioshindwa: {$importer->failed}"
                            )
                            ->success()
                            ->duration(10000)
                            ->send();

                    } catch (Throwable $exception) {

                        report($exception);

                        Notification::make()
                            ->title('Uingizaji umeshindikana')
                            ->body(
                                'Faili haikuweza kuingizwa. Hakikisha ina safu sahihi na ni CSV, XLSX au XLS.'
                            )
                            ->danger()
                            ->persistent()
                            ->send();

                    } finally {

                        if (
                            filled($storedPath) &&
                            Storage::disk('local')->exists($storedPath)
                        ) {
                            Storage::disk('local')
                                ->delete($storedPath);
                        }
                    }
                }),

            /*
            |--------------------------------------------------------------------------
            | EXPORT SUBSCRIBERS
            |--------------------------------------------------------------------------
            */
            Actions\Action::make('export')
                ->label('Hamisha Wasajili')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->modalHeading('Hamisha Wasajili')
                ->modalDescription(
                    'Chagua wasajili na aina ya faili unayotaka kupakua.'
                )
                ->modalSubmitActionLabel('Pakua')
                ->modalCancelActionLabel('Ghairi')
                ->form([

                    Select::make('status')
                        ->label('Wasajili wa kuhamisha')
                        ->options([
                            'all' => 'Wasajili wote',
                            'subscribed' => 'Waliojiandikisha tu',
                            'unsubscribed' => 'Waliojiondoa tu',
                        ])
                        ->default('subscribed')
                        ->required(),

                    Select::make('format')
                        ->label('Aina ya faili')
                        ->options([
                            'xlsx' => 'Excel (.xlsx)',
                            'csv' => 'CSV (.csv)',
                        ])
                        ->default('xlsx')
                        ->required(),
                ])
                ->action(function (array $data) {

                    $status = $data['status'] ?? 'subscribed';

                    $format = $data['format'] ?? 'xlsx';

                    $extension =
                        $format === 'csv'
                            ? 'csv'
                            : 'xlsx';

                    $writerType =
                        $format === 'csv'
                            ? ExcelFormat::CSV
                            : ExcelFormat::XLSX;

                    $filename =
                        'uzima-milele-email-subscribers-' .
                        now()->format('Y-m-d-His') .
                        '.' .
                        $extension;

                    return Excel::download(
                        new EmailSubscribersExport($status),
                        $filename,
                        $writerType
                    );
                }),

            /*
            |--------------------------------------------------------------------------
            | CREATE SUBSCRIBER
            |--------------------------------------------------------------------------
            */
            Actions\CreateAction::make()
                ->label('Ongeza Msajili')
                ->icon('heroicon-o-plus'),
        ];
    }
}