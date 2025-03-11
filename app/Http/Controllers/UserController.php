<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        // Check if user exists (Optional: Adjust logic based on your needs)

            $user = new User();
            $user->name = $request->name;
            $user->fcm_tokens = $request->fcm_token ? [$request->fcm_token] : [];
            $user->is_login = false; // Default false
            $user->save();
      
        return response()->json(['message' => 'User added successfully', 'user' => $user, 'status' => 200]);
    }
}
