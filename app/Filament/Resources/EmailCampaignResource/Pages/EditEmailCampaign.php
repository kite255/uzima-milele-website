<?php

namespace App\Filament\Resources\EmailCampaignResource\Pages;

use App\Filament\Resources\EmailCampaignResource;
use App\Models\EmailCampaign;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailCampaign extends EditRecord
{
    protected static string $resource = EmailCampaignResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent editing sent/queued campaigns
        |--------------------------------------------------------------------------
        */
        if (
            $this->record->status
            !== EmailCampaign::STATUS_DRAFT
        ) {
            abort(403);
        }

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

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Futa Kampeni')
                ->visible(
                    fn (): bool =>
                        $this->record->status
                        === EmailCampaign::STATUS_DRAFT
                ),
        ];
    }
}