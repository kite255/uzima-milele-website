<?php

namespace App\Filament\Resources\EmailCampaignTemplateResource\Pages;

use App\Filament\Resources\EmailCampaignTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailCampaignTemplate extends EditRecord
{
    protected static string $resource = EmailCampaignTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Futa'),
        ];
    }
}
