<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Services\Email\CampaignPersonalizationService;
use App\Services\Email\CampaignTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DevotionCampaignMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public EmailCampaign $campaign,
        public EmailCampaignRecipient $recipient,
    ) {
    }

    public function build(): self
    {
        $subscriber = $this->recipient->subscriber;
        $personalization = app(CampaignPersonalizationService::class);
        $tracking = app(CampaignTrackingService::class);

        $renderedSubject = $personalization->render(
            (string) $this->campaign->subject,
            $this->campaign,
            $this->recipient,
            $subscriber
        );

        $renderedHtml = view('emails.devotions.daily', [
            'devotion' => $this->campaign->devotion,
            'subscriber' => $subscriber,
            'campaign' => $this->campaign,
            'campaignRecipient' => $this->recipient,
        ])->render();

        $renderedHtml = $tracking->rewriteLinks(
            $renderedHtml,
            $this->recipient
        );

        return $this
            ->subject($renderedSubject)
            ->html($renderedHtml);
    }
}
