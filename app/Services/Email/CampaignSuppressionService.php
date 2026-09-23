<?php

namespace App\Services\Email;

use App\Models\EmailSubscriber;
use App\Models\EmailSuppression;
use Illuminate\Support\Str;

class CampaignSuppressionService
{
    public function isSuppressed(string $email): bool
    {
        return EmailSuppression::query()
            ->where('email', $this->normalizeEmail($email))
            ->exists();
    }

    public function suppress(
        string $email,
        string $reason,
        string $source,
        ?string $notes = null,
        ?int $createdBy = null
    ): EmailSuppression {
        $normalizedEmail = $this->normalizeEmail($email);

        return EmailSuppression::query()->updateOrCreate(
            [
                'email' => $normalizedEmail,
            ],
            [
                'reason' => $reason,
                'source' => $source,
                'notes' => $notes,
                'suppressed_at' => now(),
                'created_by' => $createdBy,
            ]
        );
    }

    public function removeUnsubscribeSuppression(string $email): void
    {
        EmailSuppression::query()
            ->where('email', $this->normalizeEmail($email))
            ->where('reason', EmailSuppression::REASON_UNSUBSCRIBED)
            ->delete();
    }

    public function canReceive(EmailSubscriber $subscriber): bool
    {
        $email = $this->normalizeEmail((string) $subscriber->email);

        return $subscriber->status === 'subscribed'
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && ! $this->isSuppressed($email);
    }

    protected function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }
}
