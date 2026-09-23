<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Services\Email\CampaignPersonalizationService;
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

        $renderedSubject = $personalization->render(
            (string) $this->campaign->subject,
            $this->campaign,
            $this->recipient,
            $subscriber
        );

        return $this
            ->subject($renderedSubject)
            ->view('emails.devotions.daily')
            ->with([
                'devotion' => $this->campaign->devotion,
                'subscriber' => $subscriber,
                'campaign' => $this->campaign,
                'campaignRecipient' => $this->recipient,
            ]);
    }
}
