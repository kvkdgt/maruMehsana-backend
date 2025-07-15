<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Http\Controllers\FcmController;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        $fcmController = app(FcmController::class);
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
            $this->processNotification($notification, $fcmController);
        }
        
        $this->info("Scheduled notification process completed!");
        Log::info('Scheduled notification cron completed', [
            'processed_count' => $notifications->count()
        ]);
    }
    
    /**
     * Process individual notification
     */
    private function processNotification($notification, $fcmController)
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
            $this->info("Sending to {$appUsers->count()} users");
            
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
            
            // Send to each user based on notification type
            $successCount = 0;
            $failureCount = 0;
            
            foreach ($appUsers as $user) {
                try {
                    if ($notification->type === 'news' && $notification->newsArticle) {
                        // Send news notification with special data
                        $response = $fcmController->sendNewsNotification(new Request([
                            'user_id' => $user->id,
                            'title' => $notification->title,
                            'body' => $notification->description,
                            'image' => $imageUrl,
                            'news_id' => $notification->news_article_id,
                            'news_slug' => $notification->newsArticle->slug,
                        ]), $notification->id);
                        
                        $this->info("News notification sent to user {$user->id}");
                    } else {
                        // Send regular notification
                        $response = $fcmController->sendFcmNotification(new Request([
                            'user_id' => $user->id,
                            'title' => $notification->title,
                            'body' => $notification->description,
                            'image' => $imageUrl,
                        ]), $notification->id);
                        
                        $this->info("Regular notification sent to user {$user->id}");
                    }
                    
                    $successCount++;
                } catch (\Exception $e) {
                    $failureCount++;
                    $this->error("Failed to send notification to user {$user->id}: " . $e->getMessage());
                    Log::error('Failed to send notification to user', [
                        'notification_id' => $notification->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $this->info("Notification ID {$notification->id} processing completed. Success: {$successCount}, Failures: {$failureCount}");
            
            Log::info('Notification processing completed', [
                'notification_id' => $notification->id,
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'total_users' => $appUsers->count()
            ]);
            
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
                $query->where('id',467);
                break;
            case 'active_users':
                // Example: Add filter for active users
                $query->where('is_active', true);
                break;
            // Add more audience types as needed
        }
        
        return $query->whereNotNull('fcm_tokens')->get();
    }
}