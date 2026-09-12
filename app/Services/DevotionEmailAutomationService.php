<?php

namespace App\Services;

use App\Models\Devotion;
use App\Models\EmailCampaign;
use App\Models\EmailSetting;
use Carbon\Carbon;

class DevotionEmailAutomationService
{
    public function sync(
        Devotion $devotion
    ): ?EmailCampaign {
        /*
        |--------------------------------------------------------------------------
        | Global Automation Settings
        |--------------------------------------------------------------------------
        */

        $settings = EmailSetting::current();

        if (! $settings->auto_schedule_devotions) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Find Existing Automatic Devotion Campaign
        |--------------------------------------------------------------------------
        */

        $campaign = $devotion
            ->emailCampaigns()
            ->where(
                'type',
                EmailCampaign::TYPE_DEVOTION
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Protect Completed / In-Progress Campaigns
        |--------------------------------------------------------------------------
        |
        | Once delivery has started or completed, editing the devotion must
        | never reset the campaign back to draft or schedule it again.
        |
        */

        if (
            $campaign
            && in_array(
                $campaign->status,
                [
                    EmailCampaign::STATUS_QUEUED,
                    EmailCampaign::STATUS_SENDING,
                    EmailCampaign::STATUS_SENT,
                ],
                true
            )
        ) {
            return $campaign->fresh();
        }

        /*
        |--------------------------------------------------------------------------
        | Existing Scheduled Campaign
        |--------------------------------------------------------------------------
        |
        | A scheduled campaign may still be edited. Cancel its existing
        | schedule first so its recipient snapshot is cleared safely and
        | the campaign returns to draft before being scheduled again.
        |
        */

        if (
            $campaign
            && $campaign->status
                === EmailCampaign::STATUS_SCHEDULED
        ) {
            $campaign = app(
                EmailCampaignService::class
            )->cancelScheduledCampaign(
                $campaign
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create Campaign If It Does Not Exist
        |--------------------------------------------------------------------------
        */

        if (! $campaign) {
            $campaign = new EmailCampaign();

            $campaign->devotion_id =
                $devotion->id;

            $campaign->type =
                EmailCampaign::TYPE_DEVOTION;

            $campaign->status =
                EmailCampaign::STATUS_DRAFT;
        }

        /*
        |--------------------------------------------------------------------------
        | Synchronize Campaign Information
        |--------------------------------------------------------------------------
        */

        $campaign->fill([
            'name' =>
                'Tafakari: '
                . $devotion->title,

            'subject' =>
                $devotion->title,

            'type' =>
                EmailCampaign::TYPE_DEVOTION,

            'devotion_id' =>
                $devotion->id,

            'recipient_scope' =>
                $settings
                    ->default_recipient_scope,

            'email_subscriber_group_id' =>
                $settings
                    ->email_subscriber_group_id,

            'status' =>
                EmailCampaign::STATUS_DRAFT,
        ]);

        $campaign->save();

        /*
        |--------------------------------------------------------------------------
        | Build Scheduled Date & Time
        |--------------------------------------------------------------------------
        */

        if (blank($devotion->published_at)) {
            return $campaign->fresh();
        }

        $scheduledAt = Carbon::parse(
            $devotion->published_at
                ->format('Y-m-d')
            . ' '
            . $devotion
                ->effectiveEmailSendTime(),
            config(
                'app.timezone',
                'Africa/Dar_es_Salaam'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Past Date Protection
        |--------------------------------------------------------------------------
        |
        | Old devotions must never suddenly send because an administrator
        | edited or imported them.
        |
        */

        if (
            $scheduledAt->lte(
                now(
                    config(
                        'app.timezone',
                        'Africa/Dar_es_Salaam'
                    )
                )
            )
        ) {
            return $campaign->fresh();
        }

        /*
        |--------------------------------------------------------------------------
        | Schedule Campaign
        |--------------------------------------------------------------------------
        */

        return app(
            EmailCampaignService::class
        )->scheduleCampaign(
            $campaign,
            $scheduledAt
        );
    }
}