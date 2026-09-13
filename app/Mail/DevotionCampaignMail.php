<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
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

        return $this
            ->subject($this->campaign->subject)
            ->view('emails.devotions.daily')
            ->with([
                'devotion' => $this->campaign->devotion,
                'subscriber' => $subscriber,
                'campaign' => $this->campaign,
                'campaignRecipient' => $this->recipient,
            ]);
    }
}