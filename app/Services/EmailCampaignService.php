<?php

namespace App\Services;

use App\Jobs\SendEmailCampaign;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use Carbon\Carbon;
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

            /*
            |--------------------------------------------------------------------------
            | Only drafts can be sent immediately
            |--------------------------------------------------------------------------
            */

            if (! $campaign->canSend()) {
                throw ValidationException::withMessages([
                    'campaign' =>
                        'Kampeni hii haiwezi kutumwa sasa kwa sababu si rasimu.',
                ]);
            }

            $this->validateCampaign($campaign);

            /*
            |--------------------------------------------------------------------------
            | Remove stale draft snapshots
            |--------------------------------------------------------------------------
            */

            $campaign->recipients()->delete();

            /*
            |--------------------------------------------------------------------------
            | Snapshot current active subscribers
            |--------------------------------------------------------------------------
            */

            $totalRecipients = $this->snapshotRecipients($campaign);

            if ($totalRecipients === 0) {
                throw ValidationException::withMessages([
                    'recipients' =>
                        'Hakuna wasajili hai wa kupokea kampeni hii.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Move campaign to queue
            |--------------------------------------------------------------------------
            */

            $campaign->update([
                'status' => EmailCampaign::STATUS_QUEUED,

                'total_recipients' => $totalRecipients,
                'sent_count' => 0,
                'failed_count' => 0,

                'scheduled_at' => null,
                'queued_at' => now(),
                'sent_at' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Dispatch after transaction commits
            |--------------------------------------------------------------------------
            */

            SendEmailCampaign::dispatch($campaign->id)
                ->afterCommit();

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
     * This means the campaign keeps the exact audience that existed
     * when the administrator scheduled it.
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
                    ->findOrFail($campaign->getKey());

                /*
                |--------------------------------------------------------------------------
                | Only draft campaigns may be scheduled
                |--------------------------------------------------------------------------
                */

                if (! $campaign->canSchedule()) {
                    throw ValidationException::withMessages([
                        'campaign' =>
                            'Kampeni hii haiwezi kupangwa kwa sababu si rasimu.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Validate campaign itself
                |--------------------------------------------------------------------------
                */

                $this->validateCampaign($campaign);

                /*
                |--------------------------------------------------------------------------
                | Validate scheduled date
                |--------------------------------------------------------------------------
                */

                $scheduledDate = $this->parseScheduledDate(
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
                | Remove previous draft recipient snapshots
                |--------------------------------------------------------------------------
                */

                $campaign->recipients()->delete();

                /*
                |--------------------------------------------------------------------------
                | Snapshot recipients now
                |--------------------------------------------------------------------------
                */

                $totalRecipients = $this->snapshotRecipients(
                    $campaign
                );

                if ($totalRecipients === 0) {
                    throw ValidationException::withMessages([
                        'recipients' =>
                            'Hakuna wasajili hai wa kupokea kampeni hii.',
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

                    'sent_count' => 0,
                    'failed_count' => 0,

                    'queued_at' => null,
                    'sent_at' => null,
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

    /**
     * Cancel a scheduled campaign and return it to draft.
     *
     * Recipient snapshots are removed because, after cancellation,
     * the administrator may edit and schedule the campaign again.
     */
    public function cancelScheduledCampaign(
        EmailCampaign $campaign
    ): EmailCampaign {
        return DB::transaction(
            function () use ($campaign): EmailCampaign {
                $campaign = EmailCampaign::query()
                    ->lockForUpdate()
                    ->findOrFail($campaign->getKey());

                if (! $campaign->canCancelSchedule()) {
                    throw ValidationException::withMessages([
                        'campaign' =>
                            'Kampeni hii haina ratiba inayoweza kughairiwa.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Remove scheduled recipient snapshot
                |--------------------------------------------------------------------------
                */

                $campaign->recipients()->delete();

                /*
                |--------------------------------------------------------------------------
                | Return to draft
                |--------------------------------------------------------------------------
                */

                $campaign->update([
                    'status' =>
                        EmailCampaign::STATUS_DRAFT,

                    'scheduled_at' => null,
                    'queued_at' => null,
                    'sent_at' => null,

                    'total_recipients' => 0,
                    'sent_count' => 0,
                    'failed_count' => 0,
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

    /**
     * Find scheduled campaigns whose time has arrived
     * and place them into the existing email queue.
     *
     * Returns the number of campaigns released.
     */
    public function releaseDueCampaigns(
        int $limit = 100
    ): int {
        $campaignIds = EmailCampaign::query()
            ->where(
                'status',
                EmailCampaign::STATUS_SCHEDULED
            )
            ->whereNotNull('scheduled_at')
            ->where(
                'scheduled_at',
                '<=',
                now()
            )
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->pluck('id');

        $released = 0;

        foreach ($campaignIds as $campaignId) {
            $wasReleased = $this->releaseScheduledCampaign(
                (int) $campaignId
            );

            if ($wasReleased) {
                $released++;
            }
        }

        return $released;
    }

    /**
     * Move one due scheduled campaign to queued.
     *
     * The database lock protects against two scheduler processes
     * releasing the same campaign at the same time.
     */
    protected function releaseScheduledCampaign(
        int $campaignId
    ): bool {
        return DB::transaction(
            function () use ($campaignId): bool {
                $campaign = EmailCampaign::query()
                    ->lockForUpdate()
                    ->find($campaignId);

                if (! $campaign) {
                    return false;
                }

                /*
                |--------------------------------------------------------------------------
                | Confirm it is still scheduled and actually due
                |--------------------------------------------------------------------------
                */

                if (! $campaign->isDueForSending()) {
                    return false;
                }

                /*
                |--------------------------------------------------------------------------
                | Ensure snapshot still exists
                |--------------------------------------------------------------------------
                */

                $pendingCount = $campaign
                    ->recipients()
                    ->where(
                        'status',
                        EmailCampaignRecipient::STATUS_PENDING
                    )
                    ->count();

                if ($pendingCount === 0) {
                    /*
                    |--------------------------------------------------------------------------
                    | Safety recovery
                    |--------------------------------------------------------------------------
                    |
                    | A scheduled campaign should normally already contain its
                    | snapshot. If not, rebuild it using current subscribers.
                    |
                    */

                    $campaign->recipients()->delete();

                    $pendingCount = $this->snapshotRecipients(
                        $campaign
                    );
                }

                if ($pendingCount === 0) {
                    $campaign->update([
                        'status' =>
                            EmailCampaign::STATUS_FAILED,

                        'failed_count' => 0,
                        'queued_at' => null,
                        'sent_at' => null,
                    ]);

                    return false;
                }

                /*
                |--------------------------------------------------------------------------
                | Recalculate total snapshot recipients
                |--------------------------------------------------------------------------
                */

                $totalRecipients = $campaign
                    ->recipients()
                    ->count();

                /*
                |--------------------------------------------------------------------------
                | Move scheduled -> queued
                |--------------------------------------------------------------------------
                */

                $campaign->update([
                    'status' =>
                        EmailCampaign::STATUS_QUEUED,

                    'total_recipients' =>
                        $totalRecipients,

                    'sent_count' => 0,
                    'failed_count' => 0,

                    'queued_at' => now(),
                    'sent_at' => null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Existing job handles actual delivery
                |--------------------------------------------------------------------------
                */

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

    /**
     * Copy current active subscribers into the campaign recipient table.
     *
     * The exact name and email are preserved for historical reporting.
     */
    protected function snapshotRecipients(
        EmailCampaign $campaign
    ): int {
        $total = 0;

        EmailSubscriber::query()
            ->subscribed()
            ->select([
                'id',
                'name',
                'first_name',
                'last_name',
                'email',
            ])
            ->orderBy('id')
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

                    foreach ($subscribers as $subscriber) {
                        $email = strtolower(
                            trim(
                                (string) $subscriber->email
                            )
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | Skip invalid email addresses
                        |--------------------------------------------------------------------------
                        */

                        if (
                            blank($email)
                            || ! filter_var(
                                $email,
                                FILTER_VALIDATE_EMAIL
                            )
                        ) {
                            continue;
                        }

                        $name = $subscriber->name
                            ?: trim(
                                ($subscriber->first_name ?? '')
                                . ' '
                                . ($subscriber->last_name ?? '')
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

                    /*
                    |--------------------------------------------------------------------------
                    | Unique database constraint prevents duplicates
                    |--------------------------------------------------------------------------
                    */

                    $inserted = DB::table(
                        'email_campaign_recipients'
                    )->insertOrIgnore(
                        $rows
                    );

                    $total += $inserted;
                }
            );

        return $total;
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
        | Devotion Campaign
        |--------------------------------------------------------------------------
        */

        if ($campaign->isDevotion()) {
            if (blank($campaign->devotion_id)) {
                throw ValidationException::withMessages([
                    'devotion_id' =>
                        'Chagua tafakari ya kutuma.',
                ]);
            }

            if (! $campaign->devotion()->exists()) {
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
            if (blank($campaign->content)) {
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