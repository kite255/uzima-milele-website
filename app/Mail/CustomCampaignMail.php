<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
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
        return $this
            ->subject($this->campaign->subject)
            ->view('emails.campaigns.custom')
            ->with([
                'campaign' => $this->campaign,
                'recipient' => $this->recipient,
                'subscriber' => $this->recipient->subscriber,
            ]);
    }
}