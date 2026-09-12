<?php

namespace App\Filament\Resources\EmailSubscriberGroupResource\Pages;

use App\Filament\Resources\EmailSubscriberGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailSubscriberGroup extends EditRecord
{
    protected static string $resource = EmailSubscriberGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Futa Kundi'),
        ];
    }
}