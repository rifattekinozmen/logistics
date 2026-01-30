<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type',
        'channel',
        'title',
        'content',
        'related_type',
        'related_id',
        'status',
        'sent_at',
        'is_read',
        'read_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
