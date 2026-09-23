<?php

namespace App\Services\Email;

use App\Models\EmailCampaignClick;
use App\Models\EmailCampaignRecipient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;

class CampaignTrackingService
{
    public function trackableUrl(
        EmailCampaignRecipient $recipient,
        string $destination
    ): string {
        $this->validateDestination($destination);

        if (blank($recipient->tracking_token)) {
            throw new InvalidArgumentException('Recipient tracking token is required.');
        }

        return URL::temporarySignedRoute(
            'email-campaigns.click',
            now()->addDays(30),
            [
                'token' => $recipient->tracking_token,
                'url' => $destination,
            ]
        );
    }

    public function recordClick(
        EmailCampaignRecipient $recipient,
        string $destination,
        ?string $userAgent,
        ?string $ip
    ): EmailCampaignClick {
        $this->validateDestination($destination);

        return DB::transaction(function () use (
            $recipient,
            $destination,
            $userAgent,
            $ip
        ): EmailCampaignClick {
            $lockedRecipient = EmailCampaignRecipient::query()
                ->lockForUpdate()
                ->findOrFail($recipient->getKey());

            $clickedAt = now();

            $click = EmailCampaignClick::query()->create([
                'email_campaign_id' => $lockedRecipient->email_campaign_id,
                'email_campaign_recipient_id' => $lockedRecipient->id,
                'url' => $destination,
                'clicked_at' => $clickedAt,
                'user_agent' => filled($userAgent)
                    ? mb_substr((string) $userAgent, 0, 65535)
                    : null,
                'ip_hash' => filled($ip)
                    ? hash('sha256', (string) $ip)
                    : null,
            ]);

            $lockedRecipient->update([
                'first_clicked_at' => $lockedRecipient->first_clicked_at ?? $clickedAt,
                'last_clicked_at' => $clickedAt,
                'click_count' => ((int) $lockedRecipient->click_count) + 1,
            ]);

            $recipient->refresh();

            return $click;
        });
    }

    public function isSafeDestination(string $destination): bool
    {
        if (! filter_var($destination, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($destination, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        return filled(parse_url($destination, PHP_URL_HOST));
    }

    protected function validateDestination(string $destination): void
    {
        if (! $this->isSafeDestination($destination)) {
            throw new InvalidArgumentException('Unsafe or invalid tracking destination.');
        }
    }
}
