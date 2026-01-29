<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Jobs\SendFcmNotificationJob;
use App\Models\AppUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationReportMail;
use App\Jobs\SendNotificationCompletionEmail;

class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-scheduled {--type=all : Type of notifications to send (all, manual, auto)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends scheduled notifications that are due (both manual and auto-scheduled)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        
        $this->info("Starting scheduled notification process...");
        Log::info('Scheduled notification cron started', ['type' => $type]);
        
        // Get notifications based on type
        $notifications = collect();
        
        if ($type === 'all' || $type === 'manual') {
            // Get manually scheduled notifications that are due
            $manualNotifications = Notification::manuallyScheduledDue()->get();
            $notifications = $notifications->merge($manualNotifications);
            $this->info("Found {$manualNotifications->count()} manually scheduled notifications");
        }
        
        if ($type === 'all' || $type === 'auto') {
            // Get auto-scheduled notifications that are due (like news notifications)
            $autoNotifications = Notification::autoScheduledDue()->get();
            $notifications = $notifications->merge($autoNotifications);
            $this->info("Found {$autoNotifications->count()} auto-scheduled notifications");
        }
        
        $this->info("Total notifications to process: {$notifications->count()}");
        
        if ($notifications->isEmpty()) {
            $this->info("No notifications to send at this time.");
            Log::info('No scheduled notifications found');
            return;
        }
        
        foreach ($notifications as $notification) {
            $this->processNotification($notification);
        }
        
        $this->info("Scheduled notification process completed!");
        Log::info('Scheduled notification cron completed', [
            'processed_count' => $notifications->count()
        ]);
    }
    
    /**
     * Process individual notification
     */
    private function processNotification($notification)
    {
        $scheduleType = $notification->isAutoScheduled() ? 'auto-scheduled' : 'manually scheduled';
        $this->info("Processing {$scheduleType} notification ID {$notification->id}: {$notification->title}");
        
        Log::info('Processing notification', [
            'notification_id' => $notification->id,
            'type' => $notification->type,
            'schedule_type' => $scheduleType,
            'title' => $notification->title
        ]);
        
        try {
            // Get users based on audience
            $appUsers = $this->getUsersByAudience($notification->audience);
            $this->info("Dispatching jobs for {$appUsers->count()} users");
            
            if ($appUsers->isEmpty()) {
                $this->warn("No users found for audience: {$notification->audience}");
                Log::warning('No users found for notification', [
                    'notification_id' => $notification->id,
                    'audience' => $notification->audience
                ]);
                return;
            }
            
            // Mark as sent first to prevent duplicate processing
            $notification->is_sent = true;
            $notification->save();
            
            // Get image URL
            $imageUrl = $notification->banner ? asset('storage/' . $notification->banner) : null;
            
            // Dispatch jobs for each user in chunks (background processing)
            $jobCount = 0;
            
            $appUsers->chunk(100, function ($userChunk) use ($notification, $imageUrl, &$jobCount) {
                foreach ($userChunk as $user) {
                    if ($notification->type === 'news' && $notification->newsArticle) {
                        // Dispatch news notification job
                        SendFcmNotificationJob::dispatch(
                            $user->id,
                            $notification->title,
                            $notification->description,
                            $imageUrl,
                            $notification->id,
                            $notification->news_article_id,
                            $notification->newsArticle->slug
                        );
                    } else {
                        // Dispatch regular notification job
                        SendFcmNotificationJob::dispatch(
                            $user->id,
                            $notification->title,
                            $notification->description,
                            $imageUrl,
                            $notification->id
                        );
                    }
                    $jobCount++;
                }
            });
            
            $this->info("Notification ID {$notification->id}: Dispatched {$jobCount} jobs to queue");
            
            Log::info('Notification jobs dispatched', [
                'notification_id' => $notification->id,
                'jobs_dispatched' => $jobCount,
                'total_users' => $appUsers->count()
            ]);

            // Send start email report
            $this->sendNotificationEmail($notification, [
                'total_users' => $appUsers->count(),
                'jobs_dispatched' => $jobCount,
                'sent' => 0,
                'failed' => 0,
            ], 'started');

            // Dispatch completion email job
            SendNotificationCompletionEmail::dispatch($notification->id, $appUsers->count())
                ->delay(now()->addSeconds(30));
            
        } catch (\Exception $e) {
            $this->error("Error processing notification ID {$notification->id}: " . $e->getMessage());
            Log::error('Error processing notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Revert the is_sent status if there was an error
            $notification->is_sent = false;
            $notification->save();
        }
    }
    
    /**
     * Get users based on audience type.
     */
    private function getUsersByAudience($audience)
    {
        $query = AppUser::query();
        
        // Customize this based on your audience requirements
        switch ($audience) {
            case 'all_users':
                // No filtering needed, get all users
                break;
            case 'active_users':
                // Example: Add filter for active users
                $query->where('is_active', true);
                break;
            // Add more audience types as needed
        }
        
        return $query->whereNotNull('fcm_tokens');
    }

    /**
     * Send notification email report
     */
    private function sendNotificationEmail($notification, $stats, $type = 'started')
    {
        try {
            Log::info('Attempting to send notification email', [
                'notification_id' => $notification->id,
                'type' => $type,
                'email' => 'kvkdgt12345@gmail.com',
                'mail_host' => config('mail.mailers.smtp.host')
            ]);
            
            Mail::to('kvkdgt12345@gmail.com')
                ->send(new NotificationReportMail($notification, $stats, $type));
            
            $this->info("Email report sent for notification ID {$notification->id}");
            Log::info('Notification email sent', [
                'notification_id' => $notification->id,
                'type' => $type,
                'email' => 'kvkdgt12345@gmail.com'
            ]);
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            Log::error('Failed to send notification email', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}