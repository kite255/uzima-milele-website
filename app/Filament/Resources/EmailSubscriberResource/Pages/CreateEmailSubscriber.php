<?php

namespace App\Filament\Resources\EmailSubscriberResource\Pages;

use App\Filament\Resources\EmailSubscriberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailSubscriber extends CreateRecord
{
    protected static string $resource = EmailSubscriberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = $this->prepareSubscriberData($data);

        $data['source'] = $data['source'] ?: 'admin';

        if (($data['status'] ?? 'subscribed') === 'subscribed') {
            $data['subscribed_at'] = $data['subscribed_at'] ?? now();
            $data['unsubscribed_at'] = null;
        } else {
            $data['unsubscribed_at'] = $data['unsubscribed_at'] ?? now();
        }

        return $data;
    }

    protected function prepareSubscriberData(array $data): array
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

        return $data;
    }
}