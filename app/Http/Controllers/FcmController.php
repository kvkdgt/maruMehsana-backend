<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class FcmController extends Controller
{
    public function sendFcmNotification(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string',
                'body' => 'required|string',
                'image' => 'nullable|string', // Image is now optional
            ]);
    
            $user = \App\Models\AppUser::find($request->user_id);
            // $fcmTokens = $user->fcm_tokens; // Decode JSON array
            $fcmTokens = ["c-qRlw-_SAmbixmVwTrpui:APA91bFeCDWsPAnumXMpA3lB92SIw0q2ve70oVVvS5NtbPt-o7ieeFsYZp_5JxxiWG2l1J0r_vQkn_7-nc-fyCVljtdUCb4I6h3Ieer57I1yvAdX5DQca24"]; // Decode JSON array
    
            if (!$fcmTokens || empty($fcmTokens)) {
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
    
                if ($err) {
                    $responses[] = ['token' => $fcmToken, 'status' => 'failed', 'error' => $err];
                } else {
                    $responses[] = ['token' => $fcmToken, 'status' => 'sent', 'response' => json_decode($response, true)];
                }
            }
    
            return response()->json([
                'message' => 'Notification process completed',
                'results' => $responses
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
      
    
}
