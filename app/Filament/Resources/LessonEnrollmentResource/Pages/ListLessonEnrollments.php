<?php

namespace App\Filament\Resources\LessonEnrollmentResource\Pages;

use App\Filament\Exports\LessonEnrollmentExporter;
use App\Filament\Resources\LessonEnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;

class ListLessonEnrollments extends ListRecords
{
    protected static string $resource = LessonEnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->pushHeaderActions([
                Tables\Actions\ExportAction::make('exportCurrentView')
                    ->label('Export Current View')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exporter(LessonEnrollmentExporter::class)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'admin'
                    ),
            ])
            ->pushBulkActions([
                Tables\Actions\ExportBulkAction::make('exportSelected')
                    ->label('Export Selected')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exporter(LessonEnrollmentExporter::class)
                    ->visible(
                        fn (): bool =>
                            auth()->user()?->role === 'admin'
                    ),
            ]);
    }
}
