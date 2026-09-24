<?php

namespace App\Services;

use App\Jobs\SendEmailCampaign;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use App\Services\Email\CampaignAudienceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmailCampaignService
{
    public function __construct(
        protected CampaignAudienceService $audienceService
    ) {}

    /**
     * Number of subscribers currently eligible
     * to receive email campaigns.
     */
    public function activeSubscriberCount(): int
    {
        return EmailSubscriber::query()
            ->subscribed()
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | SEND NOW
    |--------------------------------------------------------------------------
    */

    /**
     * Prepare and queue a draft campaign for immediate sending.
     */
    public function queueCampaign(EmailCampaign $campaign): EmailCampaign
    {
        return DB::transaction(function () use ($campaign): EmailCampaign {
            $campaign = EmailCampaign::query()
                ->lockForUpdate()
                ->findOrFail($campaign->getKey());

            if (! $campaign->canSend()) {
                throw ValidationException::withMessages([
                    'campaign' =>
                        'Kampeni hii haiwezi kutumwa sasa kwa sababu si rasimu.',
                ]);
            }

            $this->validateCampaign($campaign);

            $campaign->recipients()->delete();

            $totalRecipients = $this->snapshotRecipients(
                $campaign
            );

            if ($totalRecipients === 0) {
                throw ValidationException::withMessages([
                    'recipients' =>
                        $this->emptyAudienceMessage($campaign),
                ]);
            }

            $campaign->update([
                'status' =>
                    EmailCampaign::STATUS_QUEUED,

                'total_recipients' =>
                    $totalRecipients,

                'sent_count' =>
                    0,

                'failed_count' =>
                    0,

                'scheduled_at' =>
                    null,

                'queued_at' =>
                    now(),

                'sent_at' =>
                    null,
            ]);

            SendEmailCampaign::dispatch(
                $campaign->id
            )->afterCommit();

            return $campaign->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | SCHEDULE CAMPAIGN
    |--------------------------------------------------------------------------
    */

    public function scheduleCampaign(
        EmailCampaign $campaign,
        mixed $scheduledAt
    ): EmailCampaign {
        return DB::transaction(
            function () use (
                $campaign,
                $scheduledAt
            ): EmailCampaign {
                $campaign = EmailCampaign::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $campaign->getKey()
                    );

                if (! $campaign->canSchedule()) {
                    throw ValidationException::withMessages([
                        'campaign' =>
                            'Kampeni hii haiwezi kupangwa kwa sababu si rasimu.',
                    ]);
                }

                $this->validateCampaign(
                    $campaign
                );

                $scheduledDate =
                    $this->parseScheduledDate(
                        $scheduledAt
                    );

                if ($scheduledDate->lte(now())) {
                    throw ValidationException::withMessages([
                        'scheduled_at' =>
                            'Tarehe na muda wa kutuma lazima uwe mbele ya muda wa sasa.',
                    ]);
                }

                $campaign->recipients()
                    ->delete();

                $totalRecipients =
                    $this->snapshotRecipients(
                        $campaign
                    );

                if ($totalRecipients === 0) {
                    throw ValidationException::withMessages([
                        'recipients' =>
                            $this->emptyAudienceMessage(
                                $campaign
                            ),
                    ]);
                }

                $campaign->update([
                    'status' =>
                        EmailCampaign::STATUS_SCHEDULED,

                    'scheduled_at' =>
                        $scheduledDate,

                    'total_recipients' =>
                        $totalRecipients,

                    'sent_count' =>
                        0,

                    'failed_count' =>
                        0,

                    'queued_at' =>
                        null,

                    'sent_at' =>
                        null,
                ]);

                return $campaign->fresh();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL SCHEDULE
    |--------------------------------------------------------------------------
    */

    public function cancelScheduledCampaign(
        EmailCampaign $campaign
    ): EmailCampaign {
        return DB::transaction(
            function () use (
                $campaign
            ): EmailCampaign {
                $campaign = EmailCampaign::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $campaign->getKey()
                    );

                if (
                    ! $campaign
                        ->canCancelSchedule()
                ) {
                    throw ValidationException::withMessages([
                        'campaign' =>
                            'Kampeni hii haina ratiba inayoweza kughairiwa.',
                    ]);
                }

                $campaign->recipients()
                    ->delete();

                $campaign->update([
                    'status' =>
                        EmailCampaign::STATUS_DRAFT,

                    'scheduled_at' =>
                        null,

                    'queued_at' =>
                        null,

                    'sent_at' =>
                        null,

                    'total_recipients' =>
                        0,

                    'sent_count' =>
                        0,

                    'failed_count' =>
                        0,
                ]);

                return $campaign->fresh();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RELEASE DUE SCHEDULED CAMPAIGNS
    |--------------------------------------------------------------------------
    */

    public function releaseDueCampaigns(
        int $limit = 100
    ): int {
        $campaignIds = EmailCampaign::query()
            ->where(
                'status',
                EmailCampaign::STATUS_SCHEDULED
            )
            ->whereNotNull(
                'scheduled_at'
            )
            ->where(
                'scheduled_at',
                '<=',
                now()
            )
            ->orderBy(
                'scheduled_at'
            )
            ->limit(
                $limit
            )
            ->pluck(
                'id'
            );

        $released = 0;

        foreach (
            $campaignIds as $campaignId
        ) {
            if (
                $this->releaseScheduledCampaign(
                    (int) $campaignId
                )
            ) {
                $released++;
            }
        }

        return $released;
    }

    protected function releaseScheduledCampaign(
        int $campaignId
    ): bool {
        return DB::transaction(
            function () use (
                $campaignId
            ): bool {
                $campaign =
                    EmailCampaign::query()
                        ->lockForUpdate()
                        ->find(
                            $campaignId
                        );

                if (! $campaign) {
                    return false;
                }

                if (
                    ! $campaign
                        ->isDueForSending()
                ) {
                    return false;
                }

                $pendingCount =
                    $campaign
                        ->recipients()
                        ->where(
                            'status',
                            EmailCampaignRecipient::STATUS_PENDING
                        )
                        ->count();

                if ($pendingCount === 0) {
                    $campaign->recipients()
                        ->delete();

                    $pendingCount =
                        $this->snapshotRecipients(
                            $campaign
                        );
                }

                if ($pendingCount === 0) {
                    $campaign->update([
                        'status' =>
                            EmailCampaign::STATUS_FAILED,

                        'failed_count' =>
                            0,

                        'queued_at' =>
                            null,

                        'sent_at' =>
                            null,
                    ]);

                    return false;
                }

                $totalRecipients =
                    $campaign
                        ->recipients()
                        ->count();

                $campaign->update([
                    'status' =>
                        EmailCampaign::STATUS_QUEUED,

                    'total_recipients' =>
                        $totalRecipients,

                    'sent_count' =>
                        0,

                    'failed_count' =>
                        0,

                    'queued_at' =>
                        now(),

                    'sent_at' =>
                        null,
                ]);

                SendEmailCampaign::dispatch(
                    $campaign->id
                )->afterCommit();

                return true;
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RECIPIENT SNAPSHOT
    |--------------------------------------------------------------------------
    */

    protected function snapshotRecipients(
        EmailCampaign $campaign
    ): int {
        return $this->audienceService->snapshot($campaign);
    }

    /*
    |--------------------------------------------------------------------------
    | EMPTY AUDIENCE MESSAGE
    |--------------------------------------------------------------------------
    */

    protected function emptyAudienceMessage(
        EmailCampaign $campaign
    ): string {
        if (
            $campaign
                ->sendsToSelectedSubscribers()
        ) {
            return 'Hakuna wasajili waliochaguliwa wanaoweza kupokea kampeni hii.';
        }

        if (
            $campaign
                ->sendsToSubscriberGroup()
        ) {
            return 'Kundi lililochaguliwa halina wasajili hai wanaoweza kupokea kampeni hii.';
        }

        return 'Hakuna wasajili hai wa kupokea kampeni hii.';
    }

    /*
    |--------------------------------------------------------------------------
    | SCHEDULE DATE PARSER
    |--------------------------------------------------------------------------
    */

    protected function parseScheduledDate(
        mixed $value
    ): Carbon {
        try {
            return Carbon::parse(
                $value,
                config(
                    'app.timezone',
                    'UTC'
                )
            );
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'scheduled_at' =>
                    'Tarehe au muda wa kutuma si sahihi.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CAMPAIGN VALIDATION
    |--------------------------------------------------------------------------
    */

    protected function validateCampaign(
        EmailCampaign $campaign
    ): void {
        if (blank($campaign->name)) {
            throw ValidationException::withMessages([
                'name' =>
                    'Jina la kampeni linahitajika.',
            ]);
        }

        if (blank($campaign->subject)) {
            throw ValidationException::withMessages([
                'subject' =>
                    'Kichwa cha barua pepe kinahitajika.',
            ]);
        }

        if (
            ! in_array(
                $campaign->recipient_scope,
                [
                    EmailCampaign::RECIPIENT_SCOPE_SUBSCRIBED,
                    EmailCampaign::RECIPIENT_SCOPE_SELECTED,
                    EmailCampaign::RECIPIENT_SCOPE_GROUP,
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'recipient_scope' =>
                    'Aina ya wapokeaji haijatambulika.',
            ]);
        }

        if (
            $campaign
                ->sendsToSelectedSubscribers()
            && ! $campaign
                ->targetSubscribers()
                ->where(
                    'email_subscribers.status',
                    'subscribed'
                )
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'recipients' =>
                    'Chagua angalau msajili mmoja hai wa kupokea kampeni hii.',
            ]);
        }

        if (
            $campaign
                ->sendsToSubscriberGroup()
            && blank(
                $campaign
                    ->email_subscriber_group_id
            )
        ) {
            throw ValidationException::withMessages([
                'email_subscriber_group_id' =>
                    'Chagua kundi la wasajili.',
            ]);
        }

        if (
            $campaign
                ->sendsToSubscriberGroup()
            && ! $campaign
                ->subscriberGroup()
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'email_subscriber_group_id' =>
                    'Kundi la wasajili lililochaguliwa halipatikani.',
            ]);
        }

        if ($campaign->isDevotion()) {
            if (
                blank(
                    $campaign->devotion_id
                )
            ) {
                throw ValidationException::withMessages([
                    'devotion_id' =>
                        'Chagua tafakari ya kutuma.',
                ]);
            }

            if (
                ! $campaign
                    ->devotion()
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'devotion_id' =>
                        'Tafakari iliyochaguliwa haipatikani.',
                ]);
            }

            return;
        }

        if ($campaign->isCustom()) {
            if (
                blank(
                    $campaign->content
                )
            ) {
                throw ValidationException::withMessages([
                    'content' =>
                        'Andika ujumbe wa kampeni.',
                ]);
            }

            return;
        }

        throw ValidationException::withMessages([
            'type' =>
                'Aina ya kampeni haijatambulika.',
        ]);
    }
}
