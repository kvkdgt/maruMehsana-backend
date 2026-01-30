<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\AppUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiagnoseNotifications extends Command
{
    protected $signature = 'notifications:diagnose {--notification-id= : Specific notification ID to diagnose}';
    protected $description = 'Diagnose notification system issues';

    public function handle()
    {
        $this->info('=== NOTIFICATION SYSTEM DIAGNOSTICS ===');
        $this->newLine();

        // 1. Check timezone configuration
        $this->info('1. TIMEZONE CONFIGURATION');
        $this->line('   App Timezone: ' . config('app.timezone'));
        $this->line('   Current Server Time: ' . now()->format('Y-m-d H:i:s T'));
        $this->line('   Current UTC Time: ' . now()->utc()->format('Y-m-d H:i:s T'));
        $this->line('   Database Timezone: ' . DB::select("SELECT @@session.time_zone as tz")[0]->tz);
        $this->newLine();

        // 2. Check users and FCM tokens
        $this->info('2. USER & FCM TOKEN STATUS');
        $totalUsers = AppUser::count();
        $this->line('   Total Users: ' . $totalUsers);
        
        if ($totalUsers > 0) {
            // Check different FCM token scenarios
            $usersWithNonNullTokens = AppUser::whereNotNull('fcm_tokens')->count();
            $usersWithEmptyArray = AppUser::whereRaw("JSON_LENGTH(fcm_tokens) = 0")->count();
            $usersWithValidTokens = AppUser::whereRaw("JSON_LENGTH(fcm_tokens) > 0")->count();
            
            $this->line('   Users with non-null fcm_tokens: ' . $usersWithNonNullTokens);
            $this->line('   Users with empty token array: ' . $usersWithEmptyArray);
            $this->line('   Users with valid tokens: ' . $usersWithValidTokens);
            
            // Show sample user
            $sampleUser = AppUser::first();
            if ($sampleUser) {
                $this->line('   Sample User ID: ' . $sampleUser->id);
                $this->line('   Sample FCM Tokens: ' . json_encode($sampleUser->fcm_tokens));
            }
        } else {
            $this->warn('   No users found in database!');
        }
        $this->newLine();

        // 3. Check notifications
        $this->info('3. NOTIFICATION STATUS');
        $notificationId = $this->option('notification-id');
        
        if ($notificationId) {
            $notification = Notification::find($notificationId);
            if ($notification) {
                $this->diagnoseSpecificNotification($notification);
            } else {
                $this->error('   Notification ID ' . $notificationId . ' not found!');
            }
        } else {
            $this->diagnoseAllNotifications();
        }
        $this->newLine();

        // 4. Check queue status
        $this->info('4. QUEUE STATUS');
        $pendingJobs = DB::table('jobs')->count();
        $this->line('   Pending jobs in queue: ' . $pendingJobs);
        
        if ($pendingJobs > 0) {
            $fcmJobs = DB::table('jobs')
                ->where('payload', 'like', '%SendFcmNotificationJob%')
                ->count();
            $this->line('   FCM notification jobs: ' . $fcmJobs);
        }
        
        $failedJobs = DB::table('failed_jobs')->count();
        $this->line('   Failed jobs: ' . $failedJobs);
        
        if ($failedJobs > 0) {
            $this->warn('   Recent failed jobs:');
            $recentFailed = DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit(3)
                ->get(['id', 'queue', 'exception', 'failed_at']);
            
            foreach ($recentFailed as $failed) {
                $this->line('     - Job ID: ' . $failed->id . ' | Failed: ' . $failed->failed_at);
                $exceptionPreview = substr($failed->exception, 0, 100);
                $this->line('       Error: ' . $exceptionPreview . '...');
            }
        }
        $this->newLine();

        // 5. Check cron job
        $this->info('5. CRON JOB STATUS');
        $this->line('   Scheduled command: notifications:send-scheduled');
        $this->line('   Frequency: Every minute');
        $this->line('   To verify cron is running, check: sudo crontab -l');
        $this->newLine();

        // 6. Recommendations
        $this->info('6. RECOMMENDATIONS');
        if ($totalUsers == 0) {
            $this->warn('   ⚠ No users in database - notifications cannot be sent');
        }
        if ($pendingJobs > 100) {
            $this->warn('   ⚠ High number of pending jobs - queue worker may be slow');
        }
        if ($failedJobs > 0) {
            $this->warn('   ⚠ Failed jobs detected - check logs for errors');
        }
        
        $this->newLine();
        $this->info('=== DIAGNOSTICS COMPLETE ===');
    }

    private function diagnoseAllNotifications()
    {
        $totalNotifications = Notification::count();
        $this->line('   Total Notifications: ' . $totalNotifications);
        
        $pendingScheduled = Notification::where('is_sent', false)
            ->whereNotNull('scheduled_at')
            ->count();
        $this->line('   Pending Scheduled: ' . $pendingScheduled);
        
        $pendingAuto = Notification::where('is_sent', false)
            ->whereNotNull('auto_scheduled_at')
            ->count();
        $this->line('   Pending Auto-Scheduled: ' . $pendingAuto);
        
        $sent = Notification::where('is_sent', true)->count();
        $this->line('   Sent: ' . $sent);
        
        // Show recent notifications
        $recent = Notification::orderBy('created_at', 'desc')->limit(5)->get();
        if ($recent->count() > 0) {
            $this->line('   Recent notifications:');
            foreach ($recent as $n) {
                $scheduledInfo = '';
                if ($n->scheduled_at) {
                    $scheduledInfo = ' | Scheduled: ' . $n->scheduled_at->format('Y-m-d H:i:s');
                }
                if ($n->auto_scheduled_at) {
                    $scheduledInfo .= ' | Auto: ' . $n->auto_scheduled_at->format('Y-m-d H:i:s');
                }
                $this->line('     - ID: ' . $n->id . ' | ' . $n->title . ' | Sent: ' . ($n->is_sent ? 'YES' : 'NO') . $scheduledInfo);
            }
        }
    }

    private function diagnoseSpecificNotification($notification)
    {
        $this->line('   Notification ID: ' . $notification->id);
        $this->line('   Title: ' . $notification->title);
        $this->line('   Type: ' . $notification->type);
        $this->line('   Audience: ' . $notification->audience);
        $this->line('   Is Sent: ' . ($notification->is_sent ? 'YES' : 'NO'));
        $this->line('   Created: ' . $notification->created_at->format('Y-m-d H:i:s'));
        
        if ($notification->scheduled_at) {
            $this->line('   Scheduled At: ' . $notification->scheduled_at->format('Y-m-d H:i:s T'));
            $this->line('   Scheduled At (UTC): ' . $notification->scheduled_at->utc()->format('Y-m-d H:i:s T'));
            $diff = now()->diffInMinutes($notification->scheduled_at, false);
            if ($diff > 0) {
                $this->line('   Time until scheduled: ' . abs($diff) . ' minutes from now');
            } else {
                $this->warn('   Scheduled time was ' . abs($diff) . ' minutes ago!');
            }
        }
        
        if ($notification->auto_scheduled_at) {
            $this->line('   Auto-Scheduled At: ' . $notification->auto_scheduled_at->format('Y-m-d H:i:s T'));
            $this->line('   Auto-Scheduled At (UTC): ' . $notification->auto_scheduled_at->utc()->format('Y-m-d H:i:s T'));
            $diff = now()->diffInMinutes($notification->auto_scheduled_at, false);
            if ($diff > 0) {
                $this->line('   Time until auto-scheduled: ' . abs($diff) . ' minutes from now');
            } else {
                $this->warn('   Auto-scheduled time was ' . abs($diff) . ' minutes ago!');
            }
        }
        
        // Check if it should be sent
        $shouldBeSent = false;
        if ($notification->scheduled_at && $notification->scheduled_at <= now() && !$notification->is_sent) {
            $this->warn('   ⚠ This notification should have been sent already (manual schedule)!');
            $shouldBeSent = true;
        }
        if ($notification->auto_scheduled_at && $notification->auto_scheduled_at <= now() && !$notification->is_sent) {
            $this->warn('   ⚠ This notification should have been sent already (auto schedule)!');
            $shouldBeSent = true;
        }
        
        // Check logs
        $logsCount = $notification->logs()->count();
        $this->line('   Notification Logs: ' . $logsCount);
        if ($logsCount > 0) {
            $sentCount = $notification->logs()->where('status', 'sent')->count();
            $failedCount = $notification->logs()->where('status', 'failed')->count();
            $this->line('   Sent: ' . $sentCount . ' | Failed: ' . $failedCount);
        }
    }
}
