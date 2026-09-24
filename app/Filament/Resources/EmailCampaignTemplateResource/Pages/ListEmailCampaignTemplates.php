<?php

namespace App\Filament\Resources\EmailCampaignTemplateResource\Pages;

use App\Filament\Resources\EmailCampaignTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailCampaignTemplates extends ListRecords
{
    protected static string $resource = EmailCampaignTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Kiolezo Kipya'),
        ];
    }
}
