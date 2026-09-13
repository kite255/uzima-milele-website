<?php

namespace App\Filament\Resources\EmailSubscriberGroupResource\Pages;

use App\Filament\Resources\EmailSubscriberGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailSubscriberGroups extends ListRecords
{
    protected static string $resource = EmailSubscriberGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tengeneza Kundi'),
        ];
    }
}