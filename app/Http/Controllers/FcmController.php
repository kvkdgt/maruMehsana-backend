<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Models\NotificationLog;

class FcmController extends Controller
{
    public function sendFcmNotification(Request $request, $notificationId = null)
    {
        try {

            $request->validate([
                'user_id' => 'required',
                'title' => 'required|string',
                'body' => 'required|string',
                'image' => 'nullable|string', // Image is optional
            ]);
            $user = \App\Models\AppUser::find($request->user_id);
            $fcmTokens = $user->fcm_tokens; // Decode JSON array
    
            if (!$fcmTokens || empty($fcmTokens)) { 
                // Log the failure if notification ID is provided
                if ($notificationId) {
                    NotificationLog::create([
                        'notification_id' => $notificationId,
                        'app_user_id' => $user->id,
                        'status' => 'failed',
                        'error_message' => 'User does not have device tokens',
                        'device_type' => $user->device_type ?? 'unknown',
                    ]);
                }
                return response()->json(['message' => 'User does not have device tokens'], 400);
            }
    
            $title = $request->title;
            $description = $request->body;
            $image = $request->image; // Get image if available
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
    
            $responses = [];
    
            foreach ($fcmTokens as $fcmToken) {
                // Prepare notification data
                $notificationData = [
                    "title" => $title,
                    "body" => $description,
                ];
    
                // Add image to notification if provided
                if ($image) {
                    $notificationData["image"] = $image; // Add image URL for Android
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
                                "title" => $title,
                                "body" => $description
                            ],
                            "sound" => "default",
                            "content-available" => 1
                        ]
                    ]
                ];
    
                // If an image exists, add it to the payload for both Android and iOS
                if ($image) {
                    $androidData["notification"]["image"] = $image; // Android image URL
                    $apnsData["payload"]["aps"]["mutable-content"] = 1; // Enable rich media for iOS
                    $apnsData["payload"]["media-url"] = $image; // iOS image URL
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
    
                $payload = json_encode($data);
    
                // Send the notification using CURL
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                $response = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);
    
                // Parse the response
                $responseData = json_decode($response, true);
                
                // Create device type determination logic
                $deviceType = 'unknown';
                if (isset($user->device_type)) {
                    $deviceType = $user->device_type;
                } elseif (strpos($fcmToken, 'ios') !== false) {
                    $deviceType = 'ios';
                } elseif (strpos($fcmToken, 'android') !== false) {
                    $deviceType = 'android';
                }
                
                if ($err) {
                    $responses[] = ['token' => $fcmToken, 'status' => 'failed', 'error' => $err];
                    
                    // Log the failure if notification ID is provided
                    if ($notificationId) {
                        NotificationLog::create([
                            'notification_id' => $notificationId,
                            'app_user_id' => $user->id,
                            'status' => 'failed',
                            'error_message' => $err,
                            'device_type' => $deviceType,
                            'fcm_message_id' => null,
                        ]);
                    }
                } else {
                    $responses[] = ['token' => $fcmToken, 'status' => 'sent', 'response' => $responseData];
                    
                    // Log the success if notification ID is provided
                    if ($notificationId) {
                        $messageId = $responseData['name'] ?? null;
                        NotificationLog::create([
                            'notification_id' => $notificationId,
                            'app_user_id' => $user->id,
                            'status' => 'sent', // Initial status is 'sent', can be updated to 'delivered' via webhook
                            'error_message' => null,
                            'device_type' => $deviceType,
                            'fcm_message_id' => $messageId,
                        ]);
                    }
                }
            }
    
            return response()->json([
                'message' => 'Notification process completed',
                'results' => $responses
            ]);
    
        } catch (\Exception $e) {
            dd($e->getMessage());
            // Log the exception if notification ID is provided
            if ($notificationId && isset($user)) {
                NotificationLog::create([
                    'notification_id' => $notificationId,
                    'app_user_id' => $user->id ?? null,
                    'status' => 'failed',
                    'error_message' => 'Error: ' . $e->getMessage(),
                    'device_type' => $user->device_type ?? 'unknown',
                    'fcm_message_id' => null,
                ]);
            }
            
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle FCM delivery status updates (can be called by a webhook)
     */
    public function updateNotificationStatus(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|string',
                'status' => 'required|string|in:delivered,failed',
                'error' => 'nullable|string',
            ]);
            
            // Find the notification log by FCM message ID
            $log = NotificationLog::where('fcm_message_id', $request->message_id)->first();
            
            if (!$log) {
                return response()->json(['success' => false, 'message' => 'Notification log not found'], 404);
            }
            
            // Update the status
            $log->status = $request->status;
            
            if ($request->status === 'failed' && $request->has('error')) {
                $log->error_message = $request->error;
            }
            
            $log->save();
            
            return response()->json(['success' => true, 'message' => 'Status updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function sendNewsNotification(Request $request, $notificationId = null)
{
    try {
        $request->validate([
            'user_id' => 'required',
            'title' => 'required|string',
            'body' => 'required|string',
            'image' => 'nullable|string',
            'news_id' => 'required|integer',
            'news_slug' => 'required|string',
        ]);

        $user = \App\Models\AppUser::find($request->user_id);
        $fcmTokens = $user->fcm_tokens;

        if (!$fcmTokens || empty($fcmTokens)) {
            if ($notificationId) {
              
            }
            return response()->json(['message' => 'User does not have device tokens'], 400);
        }

        $title = $request->title;
        $description = $request->body;
        $image = $request->image;
        $newsId = $request->news_id;
        $newsSlug = $request->news_slug;
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

        $responses = [];

        foreach ($fcmTokens as $fcmToken) {
            // Prepare notification data
            $notificationData = [
                "title" => $title,
                "body" => $description,
            ];

            // Add image to notification if provided
            if ($image) {
                $notificationData["image"] = $image;
            }

            // Custom data for news navigation
            $customData = [
                "type" => "news",
                "news_id" => (string)$newsId,
                "news_slug" => $newsSlug,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];

            // Android-specific notification data
            $androidData = [
                "priority" => "high",
                "data" => $customData, // Custom data for Android
                "notification" => [
                    "sound" => "default",
                    "channel_id" => "high_priority_channel",
                    "default_sound" => true,
                    "default_vibrate_timings" => true,
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK"
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
                            "title" => $title,
                            "body" => $description
                        ],
                        "sound" => "default",
                        "content-available" => 1,
                        "category" => "NEWS_CATEGORY"
                    ],
                    // Custom data for iOS
                    "type" => "news",
                    "news_id" => (string)$newsId,
                    "news_slug" => $newsSlug
                ]
            ];

            // Add image support
            if ($image) {
                $androidData["notification"]["image"] = $image;
                $apnsData["payload"]["aps"]["mutable-content"] = 1;
                $apnsData["payload"]["media-url"] = $image;
            }

            // Construct the full FCM message payload
            $data = [
                "message" => [
                    "token" => $fcmToken,
                    "notification" => $notificationData,
                    "data" => $customData, // Add custom data at message level
                    "android" => $androidData,
                    "apns" => $apnsData
                ]
            ];

            $payload = json_encode($data);

            // Send the notification using CURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            $responseData = json_decode($response, true);
            
            $deviceType = 'unknown';
            if (isset($user->device_type)) {
                $deviceType = $user->device_type;
            } elseif (strpos($fcmToken, 'ios') !== false) {
                $deviceType = 'ios';
            } elseif (strpos($fcmToken, 'android') !== false) {
                $deviceType = 'android';
            }
            
            if ($err) {
                $responses[] = ['token' => $fcmToken, 'status' => 'failed', 'error' => $err];
                
                if ($notificationId) {
                  
                }
            } else {
                $responses[] = ['token' => $fcmToken, 'status' => 'sent', 'response' => $responseData];
                
                if ($notificationId) {
                    $messageId = $responseData['name'] ?? null;
                 
                }
            }
        }

        return response()->json([
            'message' => 'News notification process completed',
            'results' => $responses
        ]);

    } catch (\Exception $e) {
        if ($notificationId && isset($user)) {
            
        }
        
        return response()->json([
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}
}