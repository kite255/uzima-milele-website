<?php

namespace App\Services\Email;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignTemplate;
use InvalidArgumentException;

class CampaignTemplateService
{
    public const TYPE_DEVOTION = 'devotion';
    public const TYPE_CHILDREN_DEVOTION = 'children_devotion';
    public const TYPE_NEWSLETTER = 'newsletter';
    public const TYPE_ANNOUNCEMENT = 'announcement';
    public const TYPE_LESSON_REMINDER = 'lesson_reminder';
    public const TYPE_SPECIAL_EVENT = 'special_event';
    public const TYPE_GENERAL = 'general';

    public function allowedTypes(): array
    {
        return [
            self::TYPE_DEVOTION,
            self::TYPE_CHILDREN_DEVOTION,
            self::TYPE_NEWSLETTER,
            self::TYPE_ANNOUNCEMENT,
            self::TYPE_LESSON_REMINDER,
            self::TYPE_SPECIAL_EVENT,
            self::TYPE_GENERAL,
        ];
    }

    public function apply(
        EmailCampaign $campaign,
        EmailCampaignTemplate $template
    ): void {
        if (! $template->is_active) {
            throw new InvalidArgumentException('Inactive campaign templates cannot be applied.');
        }

        if (! in_array($template->type, $this->allowedTypes(), true)) {
            throw new InvalidArgumentException('Unsupported campaign template type.');
        }

        $campaignType = $template->type === self::TYPE_DEVOTION
            ? EmailCampaign::TYPE_DEVOTION
            : EmailCampaign::TYPE_CUSTOM;

        $campaign->update([
            'template_id' => $template->id,
            'type' => $campaignType,
            'subject' => $template->subject,
            'content' => $template->content,
        ]);
    }
}
