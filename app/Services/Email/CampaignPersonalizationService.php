<?php

namespace App\Services\Email;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use Illuminate\Support\Facades\Route;

class CampaignPersonalizationService
{
    public const SUPPORTED_KEYS = [
        'first_name',
        'last_name',
        'name',
        'email',
        'language',
        'campaign_name',
        'campaign_subject',
        'devotion_title',
        'devotion_url',
        'unsubscribe_url',
    ];

    public function render(
        string $content,
        EmailCampaign $campaign,
        ?EmailCampaignRecipient $recipient = null,
        ?EmailSubscriber $subscriber = null
    ): string {
        $variables = $this->variables($campaign, $recipient, $subscriber);

        foreach ($variables as $key => $value) {
            $content = str_replace('{{'.$key.'}}', $value, $content);
        }

        return $content;
    }

    public function variables(
        EmailCampaign $campaign,
        ?EmailCampaignRecipient $recipient = null,
        ?EmailSubscriber $subscriber = null
    ): array {
        $subscriber ??= $recipient?->subscriber;

        $name = trim((string) ($subscriber?->name ?: $recipient?->name ?: ''));
        $firstName = trim((string) ($subscriber?->first_name ?? ''));
        $lastName = trim((string) ($subscriber?->last_name ?? ''));

        if ($firstName === '' && $name !== '') {
            $firstName = explode(' ', $name)[0] ?? '';
        }

        if ($lastName === '' && $name !== '' && str_contains($name, ' ')) {
            $parts = preg_split('/\s+/', $name) ?: [];
            array_shift($parts);
            $lastName = trim(implode(' ', $parts));
        }

        $email = trim((string) ($subscriber?->email ?: $recipient?->email ?: ''));
        $language = trim((string) ($subscriber?->language ?? ''));
        $devotionTitle = trim((string) ($campaign->devotion?->title ?? ''));

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $name,
            'email' => $email,
            'language' => $language,
            'campaign_name' => (string) ($campaign->name ?? ''),
            'campaign_subject' => (string) ($campaign->subject ?? ''),
            'devotion_title' => $devotionTitle,
            'devotion_url' => $this->devotionUrl($campaign),
            'unsubscribe_url' => $this->unsubscribeUrl($subscriber),
        ];
    }

    protected function unsubscribeUrl(?EmailSubscriber $subscriber): string
    {
        if (! $subscriber || blank($subscriber->unsubscribe_token)) {
            return '';
        }

        return route(
            'email-subscribers.unsubscribe',
            $subscriber->unsubscribe_token
        );
    }

    protected function devotionUrl(EmailCampaign $campaign): string
    {
        $devotion = $campaign->devotion;

        if (! $devotion) {
            return '';
        }

        foreach (['devotions.show', 'devotion.show'] as $routeName) {
            if (Route::has($routeName)) {
                try {
                    return route($routeName, $devotion);
                } catch (\Throwable) {
                    // Fall through to the next safe option.
                }
            }
        }

        return '';
    }
}
