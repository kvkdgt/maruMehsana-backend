<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'banner',
        'audience',
        'scheduled_at',
        'is_sent',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_sent' => 'boolean',
    ];

    public function logs()
{
    return $this->hasMany(NotificationLog::class);
}
public function successCount()
{
    return $this->logs()->where('status', 'delivered')->count();
}

/**
 * Get the count of failed notifications.
 */
public function failureCount()
{
    return $this->logs()->where('status', 'failed')->count();
}
}