<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Http\Controllers\FcmController;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends scheduled notifications that are due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fcmController = app(FcmController::class);
        
        // Get all unsent scheduled notifications that are due
        $notifications = Notification::where('is_sent', false)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', Carbon::now())
            ->get();
            
        $this->info("Found {$notifications->count()} notifications to send.");
        
        foreach ($notifications as $notification) {
            $this->info("Sending notification ID {$notification->id}: {$notification->title}");
            
            // Get users based on audience
            $audience = $notification->audience;
            $appUsers = $this->getUsersByAudience($audience);
            
            $this->info("Sending to {$appUsers->count()} users");
            
            // Mark as sent
            $notification->is_sent = true;
            $notification->save();
            
            // Send to each user
            $imageUrl = $notification->banner ? asset('storage/' . $notification->banner) : null;
            
            foreach ($appUsers as $user) {
                $fcmController->sendFcmNotification(new Request([
                    'user_id' => $user->id,
                    'title' => $notification->title,
                    'body' => $notification->description,
                    'image' => $imageUrl,
                ]), $notification->id);
            }
            
            $this->info("Notification ID {$notification->id} sent successfully.");
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
}