<?php

namespace App\Services;

use App\Jobs\SendEmailCampaign;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmailCampaignService
{
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

            /*
            |--------------------------------------------------------------------------
            | Remove stale snapshots
            |--------------------------------------------------------------------------
            */

            $campaign->recipients()->delete();

            /*
            |--------------------------------------------------------------------------
            | Snapshot campaign audience
            |--------------------------------------------------------------------------
            */

            $totalRecipients = $this->snapshotRecipients(
                $campaign
            );

            if ($totalRecipients === 0) {
                throw ValidationException::withMessages([
                    'recipients' =>
                        $this->emptyAudienceMessage($campaign),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Move campaign to queue
            |--------------------------------------------------------------------------
            */

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

    /**
     * Schedule a campaign for a future date and time.
     *
     * Recipient snapshots are created at scheduling time.
     * This preserves the exact audience that existed when scheduled.
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

                /*
                |--------------------------------------------------------------------------
                | Remove previous snapshots
                |--------------------------------------------------------------------------
                */

                $campaign->recipients()
                    ->delete();

                /*
                |--------------------------------------------------------------------------
                | Snapshot campaign audience
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Mark scheduled
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Ensure snapshot still exists
                |--------------------------------------------------------------------------
                */

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
    | RECIPIENT QUERY
    |--------------------------------------------------------------------------
    */

    /**
     * Build the recipient query based on campaign audience.
     *
     * subscribed:
     * All active subscribers.
     *
     * selected:
     * Only explicitly selected active subscribers.
     *
     * group:
     * Only active subscribers belonging to the selected group.
     */
    protected function recipientQuery(
        EmailCampaign $campaign
    ): Builder {
        $query =
            EmailSubscriber::query()
                ->subscribed();

        /*
        |--------------------------------------------------------------------------
        | Selected Subscribers
        |--------------------------------------------------------------------------
        */

        if (
            $campaign
                ->sendsToSelectedSubscribers()
        ) {
            $query->whereIn(
                'email_subscribers.id',
                function (
                    $subQuery
                ) use (
                    $campaign
                ): void {
                    $subQuery
                        ->select(
                            'email_subscriber_id'
                        )
                        ->from(
                            'email_campaign_targets'
                        )
                        ->where(
                            'email_campaign_id',
                            $campaign->id
                        );
                }
            );

            return $query;
        }

        /*
        |--------------------------------------------------------------------------
        | Subscriber Group
        |--------------------------------------------------------------------------
        */

        if (
            $campaign
                ->sendsToSubscriberGroup()
        ) {
            $query->whereIn(
                'email_subscribers.id',
                function (
                    $subQuery
                ) use (
                    $campaign
                ): void {
                    $subQuery
                        ->select(
                            'email_subscriber_id'
                        )
                        ->from(
                            'email_subscriber_group_members'
                        )
                        ->where(
                            'email_subscriber_group_id',
                            $campaign
                                ->email_subscriber_group_id
                        );
                }
            );
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | RECIPIENT SNAPSHOT
    |--------------------------------------------------------------------------
    */

    protected function snapshotRecipients(
        EmailCampaign $campaign
    ): int {
        $total = 0;

        $this->recipientQuery(
            $campaign
        )
            ->select([
                'email_subscribers.id',
                'email_subscribers.name',
                'email_subscribers.first_name',
                'email_subscribers.last_name',
                'email_subscribers.email',
            ])
            ->orderBy(
                'email_subscribers.id'
            )
            ->chunkById(
                500,
                function (
                    $subscribers
                ) use (
                    $campaign,
                    &$total
                ): void {
                    $now = now();

                    $rows = [];

                    foreach (
                        $subscribers as $subscriber
                    ) {
                        $email = strtolower(
                            trim(
                                (string)
                                $subscriber->email
                            )
                        );

                        if (
                            blank($email)
                            || ! filter_var(
                                $email,
                                FILTER_VALIDATE_EMAIL
                            )
                        ) {
                            continue;
                        }

                        $name =
                            $subscriber->name
                            ?: trim(
                                (
                                    $subscriber->first_name
                                    ?? ''
                                )
                                . ' '
                                . (
                                    $subscriber->last_name
                                    ?? ''
                                )
                            );

                        $rows[] = [
                            'email_campaign_id' =>
                                $campaign->id,

                            'email_subscriber_id' =>
                                $subscriber->id,

                            'name' =>
                                $name !== ''
                                    ? $name
                                    : null,

                            'email' =>
                                $email,

                            'status' =>
                                EmailCampaignRecipient::STATUS_PENDING,

                            'sent_at' =>
                                null,

                            'failed_at' =>
                                null,

                            'error_message' =>
                                null,

                            'created_at' =>
                                $now,

                            'updated_at' =>
                                $now,
                        ];
                    }

                    if ($rows === []) {
                        return;
                    }

                    $inserted =
                        DB::table(
                            'email_campaign_recipients'
                        )->insertOrIgnore(
                            $rows
                        );

                    $total +=
                        $inserted;
                },
                'email_subscribers.id',
                'id'
            );

        return $total;
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

        /*
        |--------------------------------------------------------------------------
        | Recipient Scope
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Selected Subscribers
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Subscriber Group
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Devotion Campaign
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Custom Campaign
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Unknown Campaign Type
        |--------------------------------------------------------------------------
        */

        throw ValidationException::withMessages([
            'type' =>
                'Aina ya kampeni haijatambulika.',
        ]);
    }
}