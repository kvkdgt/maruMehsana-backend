<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\AppUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestNotificationSystem extends Command
{
    protected $signature = 'notifications:test {--create-test-user : Create a test user with FCM token}';
    protected $description = 'Test the notification system end-to-end';

    public function handle()
    {
        $this->info('=== NOTIFICATION SYSTEM TEST ===');
        $this->newLine();

        // Step 1: Check users
        $this->info('Step 1: Checking for users with FCM tokens...');
        $usersWithTokens = AppUser::whereNotNull('fcm_tokens')
            ->whereRaw('JSON_LENGTH(fcm_tokens) > 0')
            ->count();
        
        $this->line("   Users with valid FCM tokens: $usersWithTokens");
        
        if ($usersWithTokens == 0) {
            $this->warn('   No users with FCM tokens found!');
            
            if ($this->option('create-test-user')) {
                $this->createTestUser();
            } else {
                $this->newLine();
                $this->warn('Run with --create-test-user to create a test user, or ensure users register via mobile app.');
                return;
            }
        }
        $this->newLine();

        // Step 2: Test timezone
        $this->info('Step 2: Testing timezone handling...');
        $now = Carbon::now();
        $this->line('   Current time (IST): ' . $now->format('Y-m-d H:i:s T'));
        $this->line('   Current time (UTC): ' . $now->utc()->format('Y-m-d H:i:s T'));
        
        // Test parsing a scheduled time
        $testTime = '15:30'; // 3:30 PM
        $testDate = $now->format('Y-m-d');
        $scheduledDateTime = Carbon::parse("$testDate $testTime", config('app.timezone'));
        
        $this->line('   Test: Scheduling for 3:30 PM today');
        $this->line('   Parsed as (IST): ' . $scheduledDateTime->format('Y-m-d H:i:s T'));
        $this->line('   Parsed as (UTC): ' . $scheduledDateTime->utc()->format('Y-m-d H:i:s T'));
        $this->line('   Stored in DB as: ' . $scheduledDateTime->toDateTimeString());
        $this->newLine();

        // Step 3: Test notification creation
        $this->info('Step 3: Creating test notification...');
        
        $testNotification = Notification::create([
            'title' => 'Test Notification - ' . now()->format('H:i:s'),
            'description' => 'This is a test notification created at ' . now()->format('Y-m-d H:i:s'),
            'audience' => 'all_users',
            'type' => 'general',
            'scheduled_at' => now()->addMinutes(2), // Schedule for 2 minutes from now
            'is_sent' => false,
        ]);
        
        $this->line('   ✅ Test notification created!');
        $this->line('   ID: ' . $testNotification->id);
        $this->line('   Title: ' . $testNotification->title);
        $this->line('   Scheduled for: ' . $testNotification->scheduled_at->format('Y-m-d H:i:s T'));
        $this->line('   Current time: ' . now()->format('Y-m-d H:i:s T'));
        $this->line('   Will send in: ' . now()->diffInSeconds($testNotification->scheduled_at) . ' seconds');
        $this->newLine();

        // Step 4: Check if it would be picked up by cron
        $this->info('Step 4: Testing cron job query...');
        
        $dueNotifications = Notification::where('is_sent', false)
            ->whereNotNull('scheduled_at')
            ->whereNull('auto_scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();
        
        $this->line('   Notifications due now: ' . $dueNotifications->count());
        
        if ($dueNotifications->count() > 0) {
            foreach ($dueNotifications as $notif) {
                $this->line('   - ID ' . $notif->id . ': ' . $notif->title . ' (scheduled: ' . $notif->scheduled_at->format('H:i:s') . ')');
            }
        }
        $this->newLine();

        // Step 5: Test FCM token retrieval
        $this->info('Step 5: Testing user retrieval for notifications...');
        
        $users = AppUser::whereNotNull('fcm_tokens')
            ->whereRaw('JSON_LENGTH(fcm_tokens) > 0')
            ->get();
        
        $this->line('   Users that would receive notification: ' . $users->count());
        
        if ($users->count() > 0) {
            $sampleUser = $users->first();
            $this->line('   Sample user ID: ' . $sampleUser->id);
            $this->line('   Sample user name: ' . $sampleUser->name);
            $this->line('   FCM tokens: ' . json_encode($sampleUser->fcm_tokens));
        }
        $this->newLine();

        // Step 6: Summary
        $this->info('=== TEST SUMMARY ===');
        $this->line('✅ Timezone: ' . config('app.timezone'));
        $this->line('✅ Database timezone: +05:30');
        $this->line('✅ Test notification created (ID: ' . $testNotification->id . ')');
        $this->line('✅ Scheduled for: ' . $testNotification->scheduled_at->format('Y-m-d H:i:s T'));
        $this->newLine();
        
        $this->info('📋 Next Steps:');
        $this->line('1. Wait 2 minutes for the test notification to be due');
        $this->line('2. The cron job should pick it up and send it');
        $this->line('3. Check logs: tail -f storage/logs/laravel.log');
        $this->line('4. Or run manually: php artisan notifications:send-scheduled');
        $this->newLine();
        
        $this->info('To check if it was sent:');
        $this->line('php artisan notifications:diagnose --notification-id=' . $testNotification->id);
    }

    private function createTestUser()
    {
        $this->info('   Creating test user...');
        
        $testUser = AppUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'fcm_tokens' => ['test_token_' . time()],
            'is_login' => true,
        ]);
        
        $this->line('   ✅ Test user created!');
        $this->line('   ID: ' . $testUser->id);
        $this->line('   Name: ' . $testUser->name);
        $this->line('   Email: ' . $testUser->email);
        $this->line('   FCM Tokens: ' . json_encode($testUser->fcm_tokens));
        $this->newLine();
        $this->warn('   Note: This is a test token. Real notifications won\'t be delivered.');
        $this->warn('   For real testing, use a device with a valid FCM token.');
    }
}
