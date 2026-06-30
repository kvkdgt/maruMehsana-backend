<?php

namespace App\Services;

use App\Models\AppUser;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Lightweight, failure-tolerant FCM sender.
 *
 * Mirrors the proven FcmController v1 send (same credentials path / project id),
 * but is wrapped so a push failure NEVER breaks the calling flow (e.g. placing
 * an order). Sends synchronously (queue is "sync" in this project anyway).
 */
class PushNotificationService
{
    protected const PROJECT_ID = 'marumehsana-49741';

    /**
     * Send a push to all of a user's devices. Returns true on best-effort send.
     *
     * @param AppUser|int $user
     * @param array $data  Optional data payload (strings) for app-side routing.
     */
    public static function sendToUser($user, string $title, string $body, array $data = [], ?string $image = null): bool
    {
        try {
            if (is_numeric($user)) {
                $user = AppUser::find($user);
            }
            if (!$user) return false;

            $tokens = $user->fcm_tokens;
            if (!$tokens || !is_array($tokens) || count($tokens) === 0) {
                return false;
            }

            $accessToken = self::accessToken();
            if (!$accessToken) return false;

            $headers = [
                "Authorization: Bearer {$accessToken}",
                'Content-Type: application/json',
            ];

            // Force all data values to strings (FCM requirement)
            $stringData = [];
            foreach ($data as $k => $v) {
                $stringData[$k] = (string) $v;
            }
            $stringData['click_action'] = 'FLUTTER_NOTIFICATION_CLICK';

            foreach ($tokens as $fcmToken) {
                $notification = ['title' => $title, 'body' => $body];
                if ($image) $notification['image'] = $image;

                $message = [
                    'message' => [
                        'token' => $fcmToken,
                        'notification' => $notification,
                        'data' => $stringData,
                        'android' => [
                            'priority' => 'high',
                            'data' => $stringData,
                            'notification' => [
                                'sound' => 'default',
                                'channel_id' => 'high_priority_channel',
                                'default_sound' => true,
                                'default_vibrate_timings' => true,
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            ],
                        ],
                        'apns' => [
                            'headers' => ['apns-priority' => '10'],
                            'payload' => [
                                'aps' => [
                                    'alert' => ['title' => $title, 'body' => $body],
                                    'sound' => 'default',
                                    'content-available' => 1,
                                ],
                            ],
                        ],
                    ],
                ];

                self::post($message, $headers);
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('PushNotificationService failed: ' . $e->getMessage());
            return false;
        }
    }

    protected static function accessToken(): ?string
    {
        try {
            $credentialsFilePath = Storage::path('json/firebaseCreds.json');
            if (!file_exists($credentialsFilePath)) {
                Log::warning('FCM credentials file not found at ' . $credentialsFilePath);
                return null;
            }

            return Cache::remember('fcm_access_token', 3500, function () use ($credentialsFilePath) {
                $client = new GoogleClient();
                $client->setAuthConfig($credentialsFilePath);
                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                $client->refreshTokenWithAssertion();
                $token = $client->getAccessToken();
                return $token['access_token'] ?? null;
            });
        } catch (\Throwable $e) {
            Log::warning('FCM token error: ' . $e->getMessage());
            return null;
        }
    }

    protected static function post(array $message, array $headers): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/' . self::PROJECT_ID . '/messages:send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_exec($ch);
        curl_close($ch);
    }
}
