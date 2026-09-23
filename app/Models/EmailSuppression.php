<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EmailSuppression extends Model
{
    use HasFactory;

    public const REASON_UNSUBSCRIBED = 'unsubscribed';

    public const REASON_MANUAL = 'manual';

    public const REASON_INVALID_ADDRESS = 'invalid_address';

    public const REASON_REPEATED_FAILURE = 'repeated_failure';

    public const REASON_BOUNCE = 'bounce';

    public const REASON_COMPLAINT = 'complaint';

    protected $fillable = [
        'email',
        'reason',
        'source',
        'notes',
        'suppressed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'suppressed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (EmailSuppression $suppression): void {
            if (filled($suppression->email)) {
                $suppression->email = Str::lower(trim($suppression->email));
            }

            if (blank($suppression->suppressed_at)) {
                $suppression->suppressed_at = now();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
