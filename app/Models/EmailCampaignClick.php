<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaignClick extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'email_campaign_id',
        'email_campaign_recipient_id',
        'url',
        'clicked_at',
        'user_agent',
        'ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(EmailCampaignRecipient::class, 'email_campaign_recipient_id');
    }
}
