<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Google\Client as GoogleClient;
use App\Models\AppUser;
use App\Models\NotificationLog;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];
    public $timeout = 120;

    protected $userId;
    protected $title;
    protected $body;
    protected $image;
    protected $notificationId;
    protected $newsId;
    protected $newsSlug;
    protected $isNewsNotification;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $userId,
        string $title,
        string $body,
        ?string $image = null,
        ?int $notificationId = null,
        ?int $newsId = null,
        ?string $newsSlug = null
    ) {
        $this->userId = $userId;
        $this->title = $title;
        $this->body = $body;
        $this->image = $image;
        $this->notificationId = $notificationId;
        $this->newsId = $newsId;
        $this->newsSlug = $newsSlug;
        $this->isNewsNotification = !is_null($newsId);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = AppUser::find($this->userId);
            
            if (!$user) {
                Log::warning('SendFcmNotificationJob: User not found', ['user_id' => $this->userId]);
                return;
            }

            // Create notification status entry for the user if it doesn't exist
            if ($this->notificationId) {
                \App\Models\UserNotificationStatus::updateOrCreate(
                    ['app_user_id' => $user->id, 'notification_id' => $this->notificationId],
                    ['is_read' => false]
                );
            }

            $fcmTokens = $user->fcm_tokens;

            if (!$fcmTokens || empty($fcmTokens)) {
                $this->logNotification($user, 'failed', 'User does not have device tokens');
                return;
            }

            $projectId = 'marumehsana-49741';
            $credentialsFilePath = Storage::path('json/firebaseCreds.json');

            $client = new GoogleClient();
            $client->setAuthConfig($credentialsFilePath);
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $token = $client->getAccessToken();
            $access_token = $token['access_token'];

            $headers = [
                "Authorization: Bearer $access_token",
                'Content-Type: application/json'
            ];

            foreach ($fcmTokens as $fcmToken) {
                $this->sendToToken($fcmToken, $headers, $projectId, $user);
            }

        } catch (\Exception $e) {
            Log::error('SendFcmNotificationJob failed', [
                'user_id' => $this->userId,
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage()
            ]);
            
            if ($this->notificationId) {
                $this->logNotification(
                    AppUser::find($this->userId),
                    'failed',
                    'Job Error: ' . $e->getMessage()
                );
            }
            
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Send notification to a specific FCM token
     */
    private function sendToToken(string $fcmToken, array $headers, string $projectId, AppUser $user): void
    {
        $notificationData = [
            "title" => $this->title,
            "body" => $this->body,
        ];

        if ($this->image) {
            $notificationData["image"] = $this->image;
        }

        // Android-specific notification data
        $androidData = [
            "priority" => "high",
            "notification" => [
                "sound" => "default",
                "channel_id" => "high_priority_channel",
                "default_sound" => true,
                "default_vibrate_timings" => true
            ]
        ];

        // iOS-specific notification data
        $apnsData = [
            "headers" => [
                "apns-priority" => "10"
            ],
            "payload" => [
                "aps" => [
                    "alert" => [
                        "title" => $this->title,
                        "body" => $this->body
                    ],
                    "sound" => "default",
                    "content-available" => 1
                ]
            ]
        ];

        // Add image support
        if ($this->image) {
            $androidData["notification"]["image"] = $this->image;
            $apnsData["payload"]["aps"]["mutable-content"] = 1;
            $apnsData["payload"]["media-url"] = $this->image;
        }

        // Add news-specific data if this is a news notification
        if ($this->isNewsNotification) {
            $customData = [
                "type" => "news",
                "news_id" => (string)$this->newsId,
                "news_slug" => $this->newsSlug,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];

            $androidData["data"] = $customData;
            $androidData["notification"]["click_action"] = "FLUTTER_NOTIFICATION_CLICK";
            $apnsData["payload"]["aps"]["category"] = "NEWS_CATEGORY";
            $apnsData["payload"]["type"] = "news";
            $apnsData["payload"]["news_id"] = (string)$this->newsId;
            $apnsData["payload"]["news_slug"] = $this->newsSlug;
        }

        // Construct the full FCM message payload
        $data = [
            "message" => [
                "token" => $fcmToken,
                "notification" => $notificationData,
                "android" => $androidData,
                "apns" => $apnsData
            ]
        ];

        // Add custom data at message level for news notifications
        if ($this->isNewsNotification) {
            $data["message"]["data"] = [
                "type" => "news",
                "news_id" => (string)$this->newsId,
                "news_slug" => $this->newsSlug,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];
        }

        $payload = json_encode($data);

        // Send the notification using CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);

        // Determine device type
        $deviceType = $user->device_type ?? 'unknown';

        if ($err) {
            Log::error('FCM send error', [
                'user_id' => $user->id,
                'token' => substr($fcmToken, 0, 20) . '...',
                'error' => $err
            ]);
            $this->logNotification($user, 'failed', $err, $deviceType);
        } else {
            // Check if FCM returned an error
            if (isset($responseData['error'])) {
                $errorMessage = $responseData['error']['message'] ?? 'Unknown FCM error';
                Log::error('FCM response error', [
                    'user_id' => $user->id,
                    'error' => $errorMessage
                ]);
                $this->logNotification($user, 'failed', $errorMessage, $deviceType);
            } else {
                $messageId = $responseData['name'] ?? null;
                $this->logNotification($user, 'sent', null, $deviceType, $messageId);
            }
        }
    }

    /**
     * Log notification result
     */
    private function logNotification(
        ?AppUser $user,
        string $status,
        ?string $errorMessage = null,
        string $deviceType = 'unknown',
        ?string $fcmMessageId = null
    ): void {
        if (!$this->notificationId || !$user) {
            return;
        }

        NotificationLog::create([
            'notification_id' => $this->notificationId,
            'app_user_id' => $user->id,
            'status' => $status,
            'error_message' => $errorMessage,
            'device_type' => $deviceType,
            'fcm_message_id' => $fcmMessageId,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendFcmNotificationJob permanently failed', [
            'user_id' => $this->userId,
            'notification_id' => $this->notificationId,
            'error' => $exception->getMessage()
        ]);
    }
}

