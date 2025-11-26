<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Mail\NotificationReportMail;

class SendNotificationCompletionEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    protected $notificationId;
    protected $totalUsers;

    /**
     * Create a new job instance.
     */
    public function __construct(int $notificationId, int $totalUsers)
    {
        $this->notificationId = $notificationId;
        $this->totalUsers = $totalUsers;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notification = Notification::find($this->notificationId);
        
        if (!$notification) {
            Log::warning('SendNotificationCompletionEmail: Notification not found', [
                'notification_id' => $this->notificationId
            ]);
            return;
        }

        // Count unique users (not tokens)
        $processedUsers = $notification->logs()->distinct('app_user_id')->count('app_user_id');

        // Check if all users are processed
        if ($processedUsers < $this->totalUsers) {
            // Check if there are still pending jobs
            $pendingJobs = \DB::table('jobs')
                ->where('payload', 'like', '%SendFcmNotificationJob%')
                ->count();
            
            if ($pendingJobs > 0) {
                // Re-queue this job to check again in 30 seconds
                self::dispatch($this->notificationId, $this->totalUsers)
                    ->delay(now()->addSeconds(30));
                return;
            }
        }

        // Get final stats - count unique users
        $usersWithSent = $notification->logs()->where('status', 'sent')
            ->distinct('app_user_id')->count('app_user_id');
        $usersWithFailed = $notification->logs()->where('status', 'failed')
            ->whereNotIn('app_user_id', function($query) use ($notification) {
                $query->select('app_user_id')
                    ->from('notification_logs')
                    ->where('notification_id', $notification->id)
                    ->where('status', 'sent');
            })
            ->distinct('app_user_id')->count('app_user_id');

        // Calculate success rate
        $successRate = $processedUsers > 0 ? round(($usersWithSent / $processedUsers) * 100, 1) : 0;

        $stats = [
            'total_users' => $this->totalUsers,
            'sent' => $usersWithSent,
            'failed' => $usersWithFailed,
            'pending' => 0,
            'success_rate' => $successRate,
        ];

        try {
            Mail::to('kvkdgt12345@gmail.com')
                ->send(new NotificationReportMail($notification, $stats, 'completed'));

            Log::info('Notification completion email sent', [
                'notification_id' => $this->notificationId,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification completion email', [
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

