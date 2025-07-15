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
        'news_article_id',
        'type',
        'scheduled_at',
        'auto_scheduled_at',
        'is_sent',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'auto_scheduled_at' => 'datetime',
        'is_sent' => 'boolean',
    ];

    public function logs()
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function newsArticle()
    {
        return $this->belongsTo(NewsArticle::class);
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

    /**
     * Scope for auto-scheduled notifications that are due
     */
    public function scopeAutoScheduledDue($query)
    {
        return $query->where('is_sent', false)
                    ->whereNotNull('auto_scheduled_at')
                    ->where('auto_scheduled_at', '<=', now());
    }

    /**
     * Scope for manually scheduled notifications that are due
     */
    public function scopeManuallyScheduledDue($query)
    {
        return $query->where('is_sent', false)
                    ->whereNotNull('scheduled_at')
                    ->whereNull('auto_scheduled_at') // Exclude auto-scheduled ones
                    ->where('scheduled_at', '<=', now());
    }

    /**
     * Check if notification is auto-scheduled
     */
    public function isAutoScheduled()
    {
        return !is_null($this->auto_scheduled_at);
    }

    /**
     * Check if notification is manually scheduled
     */
    public function isManuallyScheduled()
    {
        return !is_null($this->scheduled_at) && is_null($this->auto_scheduled_at);
    }
}