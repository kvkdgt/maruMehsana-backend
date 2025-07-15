<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Http\Controllers\FcmController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\AppUser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
            
            $scheduledDateTime = Carbon::parse($request->scheduled_date . ' ' . $request->scheduled_time);
            $notification->scheduled_at = $scheduledDateTime;
            $notification->save();
        } else {
            // Send immediately for manual notifications
            $notification->is_sent = true;
            $notification->save();
            
            // Get users based on audience
            $appUsers = $this->getUsersByAudience($request->audience);

            // Send notification to each user
            foreach ($appUsers as $user) {
                $this->fcmController->sendFcmNotification(new Request([
                    'user_id' => $user->id,
                    'title' => $request->title,
                    'body' => $request->description,
                    'image' => $imageUrl,
                ]), $notification->id);
            }
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
        
        // Determine which FCM method to use based on notification type
        foreach ($appUsers as $user) {
            if ($notification->type === 'news' && $notification->newsArticle) {
                $this->fcmController->sendNewsNotification(new Request([
                    'user_id' => $user->id,
                    'title' => $notification->title,
                    'body' => $notification->description,
                    'image' => $imageUrl,
                    'news_id' => $notification->news_article_id,
                    'news_slug' => $notification->newsArticle->slug,
                ]), $notification->id);
            } else {
                $this->fcmController->sendFcmNotification(new Request([
                    'user_id' => $user->id,
                    'title' => $notification->title,
                    'body' => $notification->description,
                    'image' => $imageUrl,
                ]), $notification->id);
            }
        }
        
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
        
        return $query->whereNotNull('fcm_tokens')->get();
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