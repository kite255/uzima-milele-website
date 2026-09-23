<?php

namespace App\Filament\Resources\EmailCampaignTemplateResource\Pages;

use App\Filament\Resources\EmailCampaignTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailCampaignTemplate extends CreateRecord
{
    protected static string $resource = EmailCampaignTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
