<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Http\Controllers\FcmController;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\AppUser;
use Illuminate\Support\Facades\Storage;

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
        $tab = $request->tab ?? 'send';
        
        $query = Notification::query();
        
        if ($tab === 'scheduled') {
            $query->whereNotNull('scheduled_at')
                  ->orderBy('scheduled_at');
        } else {
            $query->where(function($q) {
                $q->whereNull('scheduled_at');
            })
            ->orderBy('created_at', 'desc');
        }
        
        $notifications = $query->paginate(10);
        
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
            // Mark as sent if it's an immediate notification
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
        
        // Send notification to each user
        $imageUrl = $notification->banner ? asset('storage/' . $notification->banner) : null;
        
        foreach ($appUsers as $user) {
            $this->fcmController->sendFcmNotification(new Request([
                'user_id' => $user->id,
                'title' => $notification->title,
                'body' => $notification->description,
                'image' => $imageUrl,
            ]), $notification->id);
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
        
        // Customize this based on your audience requirements
        switch ($audience) {
            case 'all':
                // No filtering needed, get all users
                break;
            case 'active':
                $query->where('last_active_at', '>=', now()->subDays(30));
                break;
            case 'inactive':
                $query->where('last_active_at', '<', now()->subDays(30))
                      ->orWhereNull('last_active_at');
                break;
            // Add more audience types as needed
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
}