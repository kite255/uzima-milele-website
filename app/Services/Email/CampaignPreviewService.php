<?php

namespace App\Services\Email;

use App\Mail\CampaignTestMail;
use App\Models\EmailCampaign;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;

class CampaignPreviewService
{
    public function __construct(
        protected CampaignPersonalizationService $personalizationService,
        protected CampaignAuditService $auditService
    ) {}

    public function render(EmailCampaign $campaign): string
    {
        $renderedContent = $this->personalizationService->render(
            (string) $campaign->content,
            $campaign
        );

        if ($campaign->isCustom()) {
            return View::make('emails.campaigns.custom', [
                'campaign' => $campaign,
                'recipient' => null,
                'subscriber' => null,
                'renderedContent' => $renderedContent,
                'isPreview' => true,
            ])->render();
        }

        return View::make('emails.devotions.daily', [
            'devotion' => $campaign->devotion,
            'subscriber' => null,
            'campaign' => $campaign,
            'campaignRecipient' => null,
            'isPreview' => true,
        ])->render();
    }

    public function sendTest(EmailCampaign $campaign, array $emails): void
    {
        $validatedEmails = collect($emails)
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter(fn ($email) => $email !== '')
            ->unique()
            ->values();

        if ($validatedEmails->isEmpty()) {
            throw ValidationException::withMessages([
                'emails' => 'Weka angalau anuani moja sahihi ya barua pepe ya majaribio.',
            ]);
        }

        foreach ($validatedEmails as $email) {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'emails' => "Anuani ya barua pepe si sahihi: {$email}",
                ]);
            }
        }

        $renderedContent = $this->personalizationService->render(
            (string) $campaign->content,
            $campaign
        );

        foreach ($validatedEmails as $email) {
            Mail::to($email)->send(
                new CampaignTestMail(
                    campaign: $campaign,
                    renderedContent: $renderedContent,
                )
            );
        }

        $this->auditService->record(
            $campaign,
            'test_email_sent',
            null,
            [
                'emails' => $validatedEmails->all(),
                'count' => $validatedEmails->count(),
            ],
            auth()->id()
        );
    }
}
