<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = new AppUser();
        $user->name = $request->name;
        $user->fcm_tokens = $request->fcm_token ? [$request->fcm_token] : [];
        $user->is_login = false; 
        $user->save();

        return response()->json(['message' => 'Guest login successful', 'user' => $user, 'status' => 200]);
    }

    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:app_users,email',
            'password' => 'required|string|min:6',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = new AppUser();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->fcm_tokens = $request->fcm_token ? [$request->fcm_token] : [];
        $user->is_login = true;
        $user->save();

        return response()->json(['message' => 'Signup successful', 'user' => $user, 'status' => 200]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = AppUser::where('email', $request->email)->first();

        if (!$user || !\Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Add FCM token if not already in list
        if ($request->fcm_token) {
            $tokens = $user->fcm_tokens ?? [];
            if (!in_array($request->fcm_token, $tokens)) {
                $tokens[] = $request->fcm_token;
                $user->fcm_tokens = $tokens;
            }
        }

        $user->is_login = true;
        $user->save();

        return response()->json(['message' => 'Login successful', 'user' => $user, 'status' => 200]);
    }

    public function upgradeGuest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:app_users,id',
            'email' => 'required|email|unique:app_users,email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = AppUser::find($request->user_id);
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->is_login = true;
        $user->save();

        return response()->json(['message' => 'Upgraded to registered user', 'user' => $user, 'status' => 200]);
    }

    public function getNotifications(Request $request)
    {
        $userId = $request->user_id;
        if (!$userId) return response()->json(['error' => 'User ID required'], 400);

        $notifications = \App\Models\UserNotificationStatus::where('app_user_id', $userId)
            ->with('notification')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $notifications
        ]);
    }

    public function markNotificationAsRead(Request $request)
    {
        $userId = $request->user_id;
        $notificationId = $request->notification_id;

        if (!$userId || !$notificationId) {
            return response()->json(['error' => 'User ID and Notification ID required'], 400);
        }

        \App\Models\UserNotificationStatus::where('app_user_id', $userId)
            ->where('notification_id', $notificationId)
            ->update(['is_read' => true]);

        return response()->json(['status' => 'success', 'message' => 'Notification marked as read']);
    }

    public function getUnreadNotificationCount(Request $request)
    {
        $userId = $request->user_id;
        if (!$userId) return response()->json(['error' => 'User ID required'], 400);

        $count = \App\Models\UserNotificationStatus::where('app_user_id', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'unread_count' => $count
        ]);
    }
}
