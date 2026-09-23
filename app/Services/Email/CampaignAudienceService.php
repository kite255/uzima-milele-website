<?php

namespace App\Services\Email;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\EmailSubscriber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignAudienceService
{
    public function __construct(
        protected CampaignSuppressionService $suppressionService
    ) {}

    public function subscriberQuery(EmailCampaign $campaign): Builder
    {
        $query = EmailSubscriber::query()
            ->subscribed()
            ->whereNotExists(function ($subQuery): void {
                $subQuery
                    ->selectRaw('1')
                    ->from('email_suppressions')
                    ->whereColumn('email_suppressions.email', 'email_subscribers.email');
            });

        $this->applyRecipientScope($query, $campaign);
        $this->applyAdvancedFilter($query, $campaign);

        return $query;
    }

    public function snapshot(EmailCampaign $campaign): int
    {
        $created = 0;
        $seenEmails = [];

        $this->subscriberQuery($campaign)
            ->select([
                'email_subscribers.id',
                'email_subscribers.name',
                'email_subscribers.first_name',
                'email_subscribers.last_name',
                'email_subscribers.email',
                'email_subscribers.status',
            ])
            ->orderBy('email_subscribers.id')
            ->chunkById(
                500,
                function ($subscribers) use ($campaign, &$created, &$seenEmails): void {
                    $rows = [];
                    $now = now();

                    foreach ($subscribers as $subscriber) {
                        if (! $this->suppressionService->canReceive($subscriber)) {
                            continue;
                        }

                        $email = Str::lower(trim((string) $subscriber->email));

                        if ($email === '' || isset($seenEmails[$email])) {
                            continue;
                        }

                        $seenEmails[$email] = true;

                        $name = $subscriber->name
                            ?: trim(($subscriber->first_name ?? '').' '.($subscriber->last_name ?? ''));

                        $rows[] = [
                            'email_campaign_id' => $campaign->id,
                            'email_subscriber_id' => $subscriber->id,
                            'name' => $name !== '' ? $name : null,
                            'email' => $email,
                            'status' => EmailCampaignRecipient::STATUS_PENDING,
                            'tracking_token' => (string) Str::uuid(),
                            'first_opened_at' => null,
                            'last_opened_at' => null,
                            'open_count' => 0,
                            'first_clicked_at' => null,
                            'last_clicked_at' => null,
                            'click_count' => 0,
                            'sent_at' => null,
                            'failed_at' => null,
                            'error_message' => null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    if ($rows === []) {
                        return;
                    }

                    $created += DB::table('email_campaign_recipients')
                        ->insertOrIgnore($rows);
                },
                'email_subscribers.id',
                'id'
            );

        return $created;
    }

    protected function applyRecipientScope(Builder $query, EmailCampaign $campaign): void
    {
        if ($campaign->sendsToSelectedSubscribers()) {
            $query->whereIn('email_subscribers.id', function ($subQuery) use ($campaign): void {
                $subQuery
                    ->select('email_subscriber_id')
                    ->from('email_campaign_targets')
                    ->where('email_campaign_id', $campaign->id);
            });

            return;
        }

        if ($campaign->sendsToSubscriberGroup()) {
            $query->whereIn('email_subscribers.id', function ($subQuery) use ($campaign): void {
                $subQuery
                    ->select('email_subscriber_id')
                    ->from('email_subscriber_group_members')
                    ->where('email_subscriber_group_id', $campaign->email_subscriber_group_id);
            });
        }
    }

    protected function applyAdvancedFilter(Builder $query, EmailCampaign $campaign): void
    {
        $filter = $campaign->audience_filter_type;
        $value = $campaign->audience_filter_value;

        if (blank($filter)) {
            return;
        }

        match ($filter) {
            'new_7_days' => $query->where('subscribed_at', '>=', now()->subDays(7)),
            'new_30_days' => $query->where('subscribed_at', '>=', now()->subDays(30)),
            'subscribed_after' => $this->applySubscribedAfter($query, $value),
            'never_received' => $this->applyNeverReceived($query),
            'never_opened' => $this->applyNeverOpened($query),
            'opened_campaign' => $this->applyCampaignEngagement($query, $value, 'opened'),
            'not_opened_campaign' => $this->applyCampaignEngagement($query, $value, 'not_opened'),
            'clicked_campaign' => $this->applyCampaignEngagement($query, $value, 'clicked'),
            'not_clicked_campaign' => $this->applyCampaignEngagement($query, $value, 'not_clicked'),
            'inactive_30_days' => $this->applyInactive($query, 30),
            'inactive_60_days' => $this->applyInactive($query, 60),
            'inactive_90_days' => $this->applyInactive($query, 90),
            default => null,
        };
    }

    protected function applySubscribedAfter(Builder $query, ?string $value): void
    {
        if (blank($value)) {
            return;
        }

        try {
            $date = Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return;
        }

        $query->where('subscribed_at', '>=', $date);
    }

    protected function applyNeverReceived(Builder $query): void
    {
        $query->whereNotExists(function ($subQuery): void {
            $subQuery
                ->selectRaw('1')
                ->from('email_campaign_recipients')
                ->whereColumn(
                    'email_campaign_recipients.email_subscriber_id',
                    'email_subscribers.id'
                )
                ->where('email_campaign_recipients.status', EmailCampaignRecipient::STATUS_SENT);
        });
    }

    protected function applyNeverOpened(Builder $query): void
    {
        $query->whereNotExists(function ($subQuery): void {
            $subQuery
                ->selectRaw('1')
                ->from('email_campaign_recipients')
                ->whereColumn(
                    'email_campaign_recipients.email_subscriber_id',
                    'email_subscribers.id'
                )
                ->whereNotNull('email_campaign_recipients.first_opened_at');
        });
    }

    protected function applyCampaignEngagement(
        Builder $query,
        ?string $campaignId,
        string $mode
    ): void {
        if (! is_numeric($campaignId)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereExists(function ($subQuery) use ($campaignId, $mode): void {
            $subQuery
                ->selectRaw('1')
                ->from('email_campaign_recipients')
                ->whereColumn(
                    'email_campaign_recipients.email_subscriber_id',
                    'email_subscribers.id'
                )
                ->where('email_campaign_recipients.email_campaign_id', (int) $campaignId)
                ->where('email_campaign_recipients.status', EmailCampaignRecipient::STATUS_SENT);

            match ($mode) {
                'opened' => $subQuery->whereNotNull('email_campaign_recipients.first_opened_at'),
                'not_opened' => $subQuery->whereNull('email_campaign_recipients.first_opened_at'),
                'clicked' => $subQuery->whereNotNull('email_campaign_recipients.first_clicked_at'),
                'not_clicked' => $subQuery->whereNull('email_campaign_recipients.first_clicked_at'),
                default => null,
            };
        });
    }

    protected function applyInactive(Builder $query, int $days): void
    {
        $cutoff = now()->subDays($days);

        $query
            ->where('subscribed_at', '<=', $cutoff)
            ->whereNotExists(function ($subQuery) use ($cutoff): void {
                $subQuery
                    ->selectRaw('1')
                    ->from('email_campaign_recipients')
                    ->whereColumn(
                        'email_campaign_recipients.email_subscriber_id',
                        'email_subscribers.id'
                    )
                    ->where(function ($engagementQuery) use ($cutoff): void {
                        $engagementQuery
                            ->where('email_campaign_recipients.last_opened_at', '>', $cutoff)
                            ->orWhere('email_campaign_recipients.last_clicked_at', '>', $cutoff);
                    });
            });
    }
}
