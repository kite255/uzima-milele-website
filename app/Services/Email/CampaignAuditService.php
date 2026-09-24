<?php

namespace App\Services\Email;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignActivityLog;

class CampaignAuditService
{
    public function record(
        EmailCampaign $campaign,
        string $action,
        ?string $description = null,
        array $metadata = [],
        ?int $performedBy = null
    ): EmailCampaignActivityLog {
        return EmailCampaignActivityLog::query()->create([
            'email_campaign_id' => $campaign->id,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'performed_by' => $performedBy,
        ]);
    }
}
