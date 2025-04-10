<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'app_user_id',
        'status',
        'error_message',
        'device_type',
        'fcm_message_id',
    ];

    /**
     * Get the notification that owns the log.
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    /**
     * Get the user that received the notification.
     */
    public function user()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }
}