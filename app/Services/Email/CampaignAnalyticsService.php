<?php

namespace App\Services\Email;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use App\Models\EmailSuppression;

class CampaignAnalyticsService
{
    public function summary(EmailCampaign $campaign): array
    {
        $recipients = $campaign->recipients();

        $totalRecipients = (int) $recipients->count();
        $sent = (int) $campaign->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_SENT)
            ->count();
        $pending = (int) $campaign->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_PENDING)
            ->count();
        $failed = (int) $campaign->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_FAILED)
            ->count();
        $suppressed = (int) $campaign->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_SUPPRESSED)
            ->count();
        $opened = (int) $campaign->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_SENT)
            ->whereNotNull('first_opened_at')
            ->count();
        $noTrackedOpen = (int) $campaign->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_SENT)
            ->whereNull('first_opened_at')
            ->count();
        $clicked = (int) $campaign->recipients()
            ->where('status', EmailCampaignRecipient::STATUS_SENT)
            ->whereNotNull('first_clicked_at')
            ->count();
        $unsubscribed = (int) $campaign->recipients()
            ->whereNotNull('unsubscribed_at')
            ->count();

        return [
            'total_recipients' => $totalRecipients,
            'sent' => $sent,
            'pending' => $pending,
            'failed' => $failed,
            'suppressed' => $suppressed,
            'opened' => $opened,
            'no_tracked_open' => $noTrackedOpen,
            'clicked' => $clicked,
            'unsubscribed' => $unsubscribed,
            'send_success_rate' => $this->percentage($sent, $totalRecipients),
            'open_rate' => $this->percentage($opened, $sent),
            'click_rate' => $this->percentage($clicked, $sent),
            'click_to_open_rate' => $this->percentage($clicked, $opened),
            'unsubscribe_rate' => $this->percentage($unsubscribed, $totalRecipients),
        ];
    }

    public function audienceHealth(): array
    {
        $totalSubscribers = EmailSubscriber::query()->count();
        $activeSubscribers = EmailSubscriber::query()
            ->where('status', 'subscribed')
            ->count();
        $suppressedEmails = EmailSuppression::query()
            ->distinct('email')
            ->count('email');

        return [
            'total_subscribers' => (int) $totalSubscribers,
            'active_subscribers' => (int) $activeSubscribers,
            'suppressed_emails' => (int) $suppressedEmails,
            'active_rate' => $this->percentage(
                (int) $activeSubscribers,
                (int) $totalSubscribers
            ),
        ];
    }

    private function percentage(int $numerator, int $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        return round(($numerator / $denominator) * 100, 1);
    }
}
