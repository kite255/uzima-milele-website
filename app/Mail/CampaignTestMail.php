<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignTestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public EmailCampaign $campaign,
        public string $renderedContent,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('[TEST] '.$this->campaign->subject)
            ->view('emails.campaigns.custom')
            ->with([
                'campaign' => $this->campaign,
                'recipient' => null,
                'subscriber' => null,
                'renderedContent' => $this->renderedContent,
                'isTestEmail' => true,
            ]);
    }
}
