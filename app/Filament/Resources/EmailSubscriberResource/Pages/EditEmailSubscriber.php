<?php

namespace App\Filament\Resources\EmailSubscriberResource\Pages;

use App\Filament\Resources\EmailSubscriberResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailSubscriber extends EditRecord
{
    protected static string $resource = EmailSubscriberResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $name = trim(
            preg_replace(
                '/\s+/',
                ' ',
                $data['name']
            )
        );

        $parts = preg_split(
            '/\s+/',
            $name,
            2
        );

        $data['name'] = $name;
        $data['first_name'] = $parts[0] ?? $name;
        $data['last_name'] = $parts[1] ?? null;

        $data['email'] = strtolower(
            trim($data['email'])
        );

        if (($data['status'] ?? null) === 'subscribed') {
            $data['subscribed_at'] =
                $data['subscribed_at']
                ?? $this->record->subscribed_at
                ?? now();

            $data['unsubscribed_at'] = null;
        }

        if (($data['status'] ?? null) === 'unsubscribed') {
            $data['unsubscribed_at'] =
                $data['unsubscribed_at']
                ?? $this->record->unsubscribed_at
                ?? now();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Futa Msajili'),
        ];
    }
}