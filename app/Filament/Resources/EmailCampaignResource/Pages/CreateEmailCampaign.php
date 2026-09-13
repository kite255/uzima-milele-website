<?php

namespace App\Filament\Resources\EmailCampaignResource\Pages;

use App\Filament\Resources\EmailCampaignResource;
use App\Models\EmailCampaign;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailCampaign extends CreateRecord
{
    protected static string $resource = EmailCampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        $data['status'] = EmailCampaign::STATUS_DRAFT;

        $data['recipient_scope'] = 'subscribed';

        $data['total_recipients'] = 0;

        $data['sent_count'] = 0;

        $data['failed_count'] = 0;

        /*
        |--------------------------------------------------------------------------
        | Clean type-specific fields
        |--------------------------------------------------------------------------
        */
        if (
            ($data['type'] ?? null)
            === EmailCampaign::TYPE_DEVOTION
        ) {
            $data['content'] = null;
        }

        if (
            ($data['type'] ?? null)
            === EmailCampaign::TYPE_CUSTOM
        ) {
            $data['devotion_id'] = null;
        }

        return $data;
    }
}