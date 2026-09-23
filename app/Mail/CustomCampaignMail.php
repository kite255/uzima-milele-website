<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Services\Email\CampaignPersonalizationService;
use App\Services\Email\CampaignTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomCampaignMail extends Mailable
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

        $renderedContent = $personalization->render(
            (string) $this->campaign->content,
            $this->campaign,
            $this->recipient,
            $subscriber
        );

        $renderedContent = $tracking->rewriteLinks(
            $renderedContent,
            $this->recipient
        );

        return $this
            ->subject($renderedSubject)
            ->view('emails.campaigns.custom')
            ->with([
                'campaign' => $this->campaign,
                'recipient' => $this->recipient,
                'subscriber' => $subscriber,
                'renderedContent' => $renderedContent,
                'isPreview' => false,
                'isTestEmail' => false,
            ]);
    }
}
