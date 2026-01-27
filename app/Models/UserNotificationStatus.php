<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_user_id',
        'notification_id',
        'is_read'
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function user()
    {
        return $this->belongsTo(AppUser::class, 'app_user_id');
    }
}
