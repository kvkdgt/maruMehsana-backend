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
        
        // Handle scheduled notification
        if ($request->has('schedule') && $request->schedule === 'yes') {
            $request->validate([
                'scheduled_date' => 'required|date',
                'scheduled_time' => 'required',
            ]);
            
            $scheduledDateTime = Carbon::parse($request->scheduled_date . ' ' . $request->scheduled_time);
            $data['scheduled_at'] = $scheduledDateTime;
        } else {
            // Mark as sent if it's an immediate notification
            $data['is_sent'] = true;
            $appUsers = AppUser::all(); // Fetch users based on audience logic

            foreach ($appUsers as $user) {
                $this->fcmController->sendFcmNotification(new Request([
                    'user_id' => $user->id,
                    'title' => $request->title,
                    'body' => $request->description,
                    'image' => $imageUrl,
                ]));
            }
            
            // Here you would typically call a method to send the notification
            // sendNotificationToUsers($data);
        }
        
        Notification::create($data);
        
        return redirect()->route('admin.notifications', ['tab' => $request->has('schedule') && $request->schedule === 'yes' ? 'scheduled' : 'send'])
            ->with('success', 'Notification ' . (isset($data['is_sent']) ? 'sent' : 'scheduled') . ' successfully!');
    }
    
    /**
     * Send a scheduled notification immediately.
     */
    public function sendNow($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->is_sent = true;
        $notification->save();
        
        // Here you would typically call a method to send the notification
        // sendNotificationToUsers($notification);
        
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
}