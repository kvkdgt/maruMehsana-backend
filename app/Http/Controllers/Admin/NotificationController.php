<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Http\Controllers\FcmController;
use App\Jobs\SendFcmNotificationJob;
use App\Jobs\SendNotificationCompletionEmail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\AppUser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationReportMail;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    protected $fcmController;

    public function __construct(FcmController $fcmController)
    {
        $this->fcmController = $fcmController;
    }

    public function index(Request $request)
    {
        $query = Notification::query();
        
        // Apply filters
        if ($request->has('type')) {
            if ($request->type === 'direct') {
                $query->whereNull('scheduled_at')->whereNull('auto_scheduled_at');
            } elseif ($request->type === 'scheduled') {
                $query->where(function($q) {
                    $q->whereNotNull('scheduled_at')->orWhereNotNull('auto_scheduled_at');
                });
            } elseif ($request->type === 'auto_scheduled') {
                $query->whereNotNull('auto_scheduled_at');
            } elseif ($request->type === 'manual_scheduled') {
                $query->whereNotNull('scheduled_at')->whereNull('auto_scheduled_at');
            }
        }
        
        if ($request->has('status')) {
            if ($request->status === 'sent') {
                $query->where('is_sent', 1);
            } elseif ($request->status === 'pending') {
                $query->where('is_sent', 0);
            }
        }
        
        if ($request->has('image')) {
            if ($request->image === 'with') {
                $query->whereNotNull('banner');
            } elseif ($request->image === 'without') {
                $query->whereNull('banner');
            }
        }

        // Filter by notification type
        if ($request->has('notification_type')) {
            $query->where('type', $request->notification_type);
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->paginate(10);
        $tab = $request->tab ?? 'send';
        
        return view('admin.notifications.index', compact('notifications', 'tab'));
    }

    /**
     * Store a newly created notification in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'audience' => 'required|string',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $data = $request->except('banner');
        $data['type'] = 'general'; // Set default type
        $imageUrl = null;
        
        // Handle banner upload
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('notifications', 'public');
            $imageUrl = asset('storage/' . $bannerPath); 
            $data['banner'] = $bannerPath;
        }
        
        // Create the notification
        $notification = Notification::create($data);
        
        // Handle scheduled notification
        if ($request->has('schedule') && $request->schedule === 'yes') {
            $request->validate([
                'scheduled_date' => 'required|date',
                'scheduled_time' => 'required',
            ]);
            
            // Parse the datetime in the application's timezone (Asia/Kolkata)
            $scheduledDateTime = Carbon::parse(
                $request->scheduled_date . ' ' . $request->scheduled_time,
                config('app.timezone')
            );
            
            $notification->scheduled_at = $scheduledDateTime;
            $notification->save();
            
            // Log the scheduled time for debugging
            Log::info('Notification scheduled', [
                'notification_id' => $notification->id,
                'input_date' => $request->scheduled_date,
                'input_time' => $request->scheduled_time,
                'scheduled_at_ist' => $scheduledDateTime->format('Y-m-d H:i:s T'),
                'scheduled_at_utc' => $scheduledDateTime->utc()->format('Y-m-d H:i:s T'),
                'current_time_ist' => now()->format('Y-m-d H:i:s T'),
                'timezone' => config('app.timezone')
            ]);
        } else {
            // Send immediately for manual notifications
            $notification->is_sent = true;
            $notification->save();
            
            // Get users based on audience
            $appUsers = $this->getUsersByAudience($request->audience);
            $totalUsers = $appUsers->count();

            // Process users in chunks to prevent memory issues
            $appUsers->chunk(100, function ($userChunk) use ($request, $imageUrl, $notification) {
                foreach ($userChunk as $user) {
                    SendFcmNotificationJob::dispatch(
                        $user->id,
                        $request->title,
                        $request->description,
                        $imageUrl,
                        $notification->id
                    );
                }
            });

            // Send start email report
            $this->sendNotificationEmail($notification, [
                'total_users' => $totalUsers,
                'jobs_dispatched' => $totalUsers,
                'sent' => 0,
                'failed' => 0,
            ], 'started');

            // Dispatch completion email job (will wait and check for completion)
            SendNotificationCompletionEmail::dispatch($notification->id, $totalUsers)
                ->delay(now()->addSeconds(30));
        }
        
        return redirect()->route('admin.notifications', ['tab' => $request->has('schedule') && $request->schedule === 'yes' ? 'scheduled' : 'send'])
            ->with('success', 'Notification ' . ($notification->is_sent ? 'sent' : 'scheduled') . ' successfully!');
    }

    /**
     * Send a scheduled notification immediately.
     */
    public function sendNow($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->is_sent = true;
        $notification->save();
        
        // Get users based on audience
        $appUsers = $this->getUsersByAudience($notification->audience);
        $imageUrl = $notification->banner ? asset('storage/' . $notification->banner) : null;
        $totalUsers = $appUsers->count();
        
        // Process users in chunks to prevent memory issues and timeouts
        $appUsers->chunk(100, function ($userChunk) use ($notification, $imageUrl) {
            foreach ($userChunk as $user) {
                if ($notification->type === 'news' && $notification->newsArticle) {
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
                    SendFcmNotificationJob::dispatch(
                        $user->id,
                        $notification->title,
                        $notification->description,
                        $imageUrl,
                        $notification->id
                    );
                }
            }
        });

        // Send start email report
        $this->sendNotificationEmail($notification, [
            'total_users' => $totalUsers,
            'jobs_dispatched' => $totalUsers,
            'sent' => 0,
            'failed' => 0,
        ], 'started');

        // Dispatch completion email job
        SendNotificationCompletionEmail::dispatch($notification->id, $totalUsers)
            ->delay(now()->addSeconds(30));
        
        return redirect()->route('admin.notifications', ['tab' => 'scheduled'])
            ->with('success', 'Notification sent successfully!');
    }

    /**
     * Delete the specified notification.
     */
    public function delete($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Delete banner if exists
        if ($notification->banner) {
            Storage::disk('public')->delete($notification->banner);
        }
        
        $notification->delete();
        
        return redirect()->back()
            ->with('success', 'Notification deleted successfully!');
    }

    /**
     * Get users based on audience type.
     */
    private function getUsersByAudience($audience)
    {
        $query = AppUser::query();
        
        switch ($audience) {
            case 'all_users':
                // No filtering needed, get all users
                break;
        }
        
        // Filter for users with valid FCM tokens (non-empty JSON arrays)
        // This checks that fcm_tokens is not null AND has at least one token
        return $query->whereNotNull('fcm_tokens')
                     ->whereRaw('JSON_LENGTH(fcm_tokens) > 0');
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
            
            Log::info('Notification email sent', [
                'notification_id' => $notification->id,
                'type' => $type,
                'email' => 'kvkdgt12345@gmail.com'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification email', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show notification logs for a specific notification.
     */
    public function showLogs($id)
    {
        $notification = Notification::with('logs.user')->findOrFail($id);
        $logs = $notification->logs()->paginate(50);
        
        $stats = [
            'total' => $notification->logs()->count(),
            'sent' => $notification->logs()->where('status', 'sent')->count(),
            'delivered' => $notification->logs()->where('status', 'delivered')->count(),
            'failed' => $notification->logs()->where('status', 'failed')->count(),
        ];
        
        return view('admin.notifications.logs', compact('notification', 'logs', 'stats'));
    }

    /**
     * Get notification progress (AJAX endpoint)
     */
    public function getProgress($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Get total users for this audience
        $totalUsers = $this->getUsersByAudience($notification->audience)->count();
        
        // Count unique users who received notification (not tokens)
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
        
        // Total unique users processed
        $processedUsers = $notification->logs()->distinct('app_user_id')->count('app_user_id');
        $pendingUsers = max(0, $totalUsers - $processedUsers);
        
        // Check pending jobs in queue for THIS notification
        $pendingJobs = \DB::table('jobs')
            ->where('payload', 'like', '%SendFcmNotificationJob%')
            ->where('payload', 'like', '%"notificationId":' . $notification->id . '%')
            ->count();
        
        // Also check with different JSON format
        if ($pendingJobs == 0) {
            $pendingJobs = \DB::table('jobs')
                ->where('payload', 'like', '%SendFcmNotificationJob%')
                ->where('payload', 'like', '%notification_id%' . $notification->id . '%')
                ->count();
        }
        
        $isComplete = ($processedUsers >= $totalUsers) || ($notification->is_sent && $pendingJobs == 0 && $processedUsers > 0);
        
        // Calculate progress percentage
        $progress = $totalUsers > 0 ? min(100, round(($processedUsers / $totalUsers) * 100, 1)) : 0;
        
        return response()->json([
            'notification_id' => $notification->id,
            'title' => $notification->title,
            'total_users' => $totalUsers,
            'sent' => $usersWithSent,
            'failed' => $usersWithFailed,
            'processed' => $processedUsers,
            'pending' => $pendingUsers,
            'pending_jobs' => $pendingJobs,
            'progress' => $progress,
            'is_complete' => $isComplete,
            'is_sent' => $notification->is_sent,
            'success_rate' => $processedUsers > 0 ? round(($usersWithSent / $processedUsers) * 100, 1) : 0,
        ]);
    }

    /**
     * Get all active notification progresses - only those with pending jobs
     */
    public function getAllProgress()
    {
        // Check if there are any pending FCM jobs
        $pendingJobCount = \DB::table('jobs')
            ->where('payload', 'like', '%SendFcmNotificationJob%')
            ->count();
        
        if ($pendingJobCount == 0) {
            return response()->json(['notifications' => [], 'has_active' => false]);
        }
        
        // Get recent sent notifications that might still be processing
        $notifications = Notification::where('is_sent', true)
            ->where('updated_at', '>=', now()->subHours(1))
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
        
        $progresses = [];
        
        foreach ($notifications as $notification) {
            $totalUsers = $this->getUsersByAudience($notification->audience)->count();
            $processedUsers = $notification->logs()->distinct('app_user_id')->count('app_user_id');
            
            // Only include if not complete
            if ($processedUsers < $totalUsers) {
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
                
                $progresses[] = [
                    'notification_id' => $notification->id,
                    'title' => $notification->title,
                    'total_users' => $totalUsers,
                    'sent' => $usersWithSent,
                    'failed' => $usersWithFailed,
                    'processed' => $processedUsers,
                    'pending' => max(0, $totalUsers - $processedUsers),
                    'progress' => $totalUsers > 0 ? min(100, round(($processedUsers / $totalUsers) * 100, 1)) : 0,
                    'is_complete' => false,
                    'created_at' => $notification->created_at->format('M d, Y h:i A'),
                ];
            }
        }
        
        return response()->json([
            'notifications' => $progresses,
            'has_active' => count($progresses) > 0
        ]);
    }

    /**
     * Schedule news notification for 5 minutes later (NEW METHOD)
     */
    // public function scheduleNewsNotification($newsArticle)
    // {
    //     try {
    //         // Calculate auto-schedule time (5 minutes from now)
    //         $autoScheduledAt = Carbon::now()->addMinutes(5);
            
    //         // Create notification record with auto-scheduling
    //         $notification = Notification::create([
    //             'title' => $newsArticle->title,
    //             'description' => $newsArticle->excerpt,
    //             'audience' => 'all_users',
    //             'banner' => $newsArticle->image,
    //             'news_article_id' => $newsArticle->id,
    //             'type' => 'news',
    //             'auto_scheduled_at' => $autoScheduledAt, // Auto-schedule for 5 minutes later
    //             'is_sent' => false, // Not sent yet
    //         ]);

    //         Log::info("News notification scheduled", [
    //             'notification_id' => $notification->id,
    //             'news_id' => $newsArticle->id,
    //             'scheduled_for' => $autoScheduledAt->format('Y-m-d H:i:s'),
    //             'title' => $newsArticle->title
    //         ]);

    //         return [
    //             'success' => true, 
    //             'message' => 'News notification scheduled for ' . $autoScheduledAt->format('Y-m-d H:i:s'),
    //             'notification_id' => $notification->id,
    //             'scheduled_at' => $autoScheduledAt
    //         ];
    //     } catch (\Exception $e) {
    //         Log::error('News notification scheduling error: ' . $e->getMessage(), [
    //             'news_id' => $newsArticle->id ?? null,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
            
    //         return [
    //             'success' => false, 
    //             'message' => 'Failed to schedule news notification: ' . $e->getMessage()
    //         ];
    //     }
    // }
    public function scheduleNewsNotification($newsArticle)
{
    try {
        // Calculate auto-schedule time (5 minutes from now)
        $autoScheduledAt = Carbon::now()->addMinutes(5);
        
        // Clean the description
        $description = $newsArticle->excerpt ?: ('New article: ' . $newsArticle->title);
        $description = trim($description);
        
        Log::info("Using raw SQL for Gujarati text", [
            'description' => $description,
            'length' => strlen($description)
        ]);
        
        // Use raw SQL with parameter binding to avoid parsing issues
        $notificationId = DB::table('notifications')->insertGetId([
            'title' => $newsArticle->title,
            'description' => $description,
            'audience' => 'all_users',
            'banner' => 'news/'.$newsArticle->image,
            'news_article_id' => $newsArticle->id,
            'type' => 'general',
            'auto_scheduled_at' => $autoScheduledAt,
            'is_sent' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Log::info("News notification scheduled via raw SQL", [
            'notification_id' => $notificationId,
            'news_id' => $newsArticle->id,
            'scheduled_for' => $autoScheduledAt->format('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'message' => 'News notification scheduled for ' . $autoScheduledAt->format('Y-m-d H:i:s'),
            'notification_id' => $notificationId,
            'scheduled_at' => $autoScheduledAt
        ];
    } catch (\Exception $e) {
        Log::error('Raw SQL notification scheduling error', [
            'news_id' => $newsArticle->id ?? null,
            'error' => $e->getMessage(),
            'description' => $newsArticle->excerpt,
            'title' => $newsArticle->title
        ]);
        
        return [
            'success' => false,
            'message' => 'Failed to schedule notification: ' . $e->getMessage()
        ];
    }
}

    /**
     * Legacy method - now calls scheduleNewsNotification
     * @deprecated Use scheduleNewsNotification instead
     */
    public function sendNewsNotification($newsArticle)
    {
        return $this->scheduleNewsNotification($newsArticle);
    }
}