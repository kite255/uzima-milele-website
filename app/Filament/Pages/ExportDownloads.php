<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ExportDownloads extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationLabel = 'Export Downloads';

    protected static ?string $title = 'Export Downloads';

    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.export-downloads';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    protected function getViewData(): array
    {
        return [
            'exports' => DB::table('exports')
                ->where('user_id', auth()->id())
                ->whereNotNull('completed_at')
                ->latest('id')
                ->limit(50)
                ->get(),
        ];
    }
}
