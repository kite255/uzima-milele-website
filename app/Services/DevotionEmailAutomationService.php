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
        $settings = EmailSetting::current();

        if (! $settings->auto_schedule_devotions) {
            return null;
        }

        $campaign = $devotion
            ->emailCampaigns()
            ->firstOrNew([
                'type' =>
                    EmailCampaign::TYPE_DEVOTION,
            ]);

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

        if ($scheduledAt->lte(now())) {
            return $campaign->fresh();
        }

        return app(
            EmailCampaignService::class
        )->scheduleCampaign(
            $campaign,
            $scheduledAt
        );
    }
}