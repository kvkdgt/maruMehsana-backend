<?php
/*
 * (c) Antigravity AI
 * Implementation of notifications:send-random command
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Jobs\SendFcmNotificationJob;
use App\Models\AppUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendRandomNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-random';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends a random notification from the static library to all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting random notification process...");
        Log::info('Random notification process started');

        try {
            // 1. Load the library
            if (!Storage::disk('local')->exists('json/notification_library.json')) {
                $this->error("Notification library JSON not found at storage/app/json/notification_library.json");
                return 1;
            }

            $jsonContent = Storage::disk('local')->get('json/notification_library.json');
            $library = json_decode($jsonContent, true);

            if (!isset($library['titles']) || !isset($library['descriptions'])) {
                $this->error("Invalid library format.");
                return 1;
            }

            // 2. Pick random title and description
            $randomTitle = $library['titles'][array_rand($library['titles'])];
            $randomDescription = $library['descriptions'][array_rand($library['descriptions'])];

            $this->info("Picked Title: {$randomTitle}");
            $this->info("Picked Description: {$randomDescription}");

            // 3. Create a Notification record
            $notification = Notification::create([
                'title' => $randomTitle,
                'description' => $randomDescription,
                'audience' => 'all_users',
                'type' => 'auto',
                'is_sent' => true, // Mark as sent since we are dispatching now
                'auto_scheduled_at' => now(),
            ]);

            // 4. Dispatch jobs for all AppUsers in chunks
            $this->info("Fetching all app users...");
            $totalProcessed = 0;

            AppUser::chunk(100, function ($users) use ($notification, &$totalProcessed) {
                foreach ($users as $user) {
                    SendFcmNotificationJob::dispatch(
                        $user->id,
                        $notification->title,
                        $notification->description,
                        null, // image
                        $notification->id
                    );
                    $totalProcessed++;
                }
            });

            $this->info("Successfully dispatched jobs to {$totalProcessed} users.");
            Log::info('Random notification process completed', [
                'notification_id' => $notification->id,
                'users_targeted' => $totalProcessed
            ]);

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error('Random notification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }
}
