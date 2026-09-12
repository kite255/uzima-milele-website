<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class EmailSubscriberGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(
            EmailSubscriber::class,
            'email_subscriber_group_members',
            'email_subscriber_group_id',
            'email_subscriber_id'
        )->withTimestamps();
    }

    public function activeSubscribers(): BelongsToMany
    {
        return $this->subscribers()
            ->where(
                'email_subscribers.status',
                'subscribed'
            );
    }
}