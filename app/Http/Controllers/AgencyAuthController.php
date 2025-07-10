<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AgencyAdmin;
use App\Models\NewsAgency;

class AgencyAuthController extends Controller
{
    /**
     * Show the agency admin login form
     */
    public function showLoginForm()
    {
        // If already authenticated, redirect to dashboard
        if (Auth::guard('agency')->check()) {
            return redirect()->route('agency.dashboard');
        }
        
        return view('agency.auth.login');
    }

    /**
     * Handle agency admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ]);

        $credentials = $request->only('username', 'password');
        
        // Attempt to authenticate using the agency guard
        if (Auth::guard('agency')->attempt($credentials)) {
            $admin = Auth::guard('agency')->user();
            
            // Check if admin is active
            if (!$admin->status) {
                Auth::guard('agency')->logout();
                return back()->withErrors(['Your account has been deactivated. Please contact support.']);
            }
            
            // Check if associated agency is active
            if (!$admin->agency || !$admin->agency->status) {
                Auth::guard('agency')->logout();
                return back()->withErrors(['Your agency has been deactivated. Please contact support.']);
            }
            
            $request->session()->regenerate();
            
            // Log the successful login
            \Log::info('Agency admin logged in successfully', [
                'admin_id' => $admin->id,
                'admin_username' => $admin->username,
                'agency_id' => $admin->agency_id,
                'agency_name' => $admin->agency->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return redirect()->intended(route('agency.dashboard'));
        }

        // Log failed login attempt
        \Log::warning('Failed agency admin login attempt', [
            'username' => $request->username,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return back()->withErrors(['Invalid username or password.'])->withInput($request->only('username'));
    }

    /**
     * Handle agency admin logout
     */
    public function logout(Request $request)
    {
        $admin = Auth::guard('agency')->user();
        
        // Log the logout
        if ($admin) {
            \Log::info('Agency admin logged out', [
                'admin_id' => $admin->id,
                'admin_username' => $admin->username,
                'agency_id' => $admin->agency_id,
                'ip_address' => $request->ip()
            ]);
        }
        
        Auth::guard('agency')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('agency.login')->with('message', 'You have been logged out successfully.');
    }

    /**
     * Show the agency admin dashboard
     */
    public function dashboard()
    {
        $admin = Auth::guard('agency')->user();
        $agency = $admin->agency;
        
        // You can add dashboard statistics here
        $stats = [
            'total_articles' => 0, // Replace with actual article count
            'published_today' => 0, // Replace with today's published articles
            'draft_articles' => 0, // Replace with draft count
            'total_views' => 0, // Replace with total views
        ];
        
        return view('agency.dashboard', compact('admin', 'agency', 'stats'));
    }

    /**
     * Show agency admin profile
     */
    public function profile()
    {
        $admin = Auth::guard('agency')->user();
        $agency = $admin->agency;
        
        return view('agency.profile', compact('admin', 'agency'));
    }

    /**
     * Update agency admin profile
     */
    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('agency')->user();
        $agency = $admin->agency;
        
        $request->validate([
            // Admin fields
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:agency_admins,email,' . $admin->id,
            'admin_phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
            
            // Agency fields
            'agency_name' => 'required|string|max:255',
            'agency_email' => 'required|email|unique:news_agencies,email,' . $agency->id,
            'agency_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'admin_name.required' => 'Administrator name is required.',
            'admin_email.required' => 'Administrator email is required.',
            'admin_email.email' => 'Please enter a valid email address.',
            'admin_email.unique' => 'This email is already taken.',
            'new_password.min' => 'Password must be at least 6 characters.',
            'new_password.confirmed' => 'Password confirmation does not match.',
            'current_password.required_with' => 'Current password is required when setting a new password.',
            'agency_name.required' => 'Agency name is required.',
            'agency_email.required' => 'Agency email is required.',
            'agency_email.unique' => 'This agency email is already taken.',
            'agency_logo.image' => 'Agency logo must be an image.',
            'agency_logo.mimes' => 'Agency logo must be a file of type: jpeg, png, jpg, gif.',
            'agency_logo.max' => 'Agency logo may not be greater than 2MB.',
        ]);

        // Verify current password if new password is provided
        if ($request->new_password) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
        }

        \DB::beginTransaction();
        try {
            // Handle agency logo upload
            $logoPath = $agency->logo;
            if ($request->hasFile('agency_logo')) {
                // Delete old logo if exists
                if ($agency->logo && \Storage::exists('public/' . $agency->logo)) {
                    \Storage::delete('public/' . $agency->logo);
                }
                
                // Store new logo
                $logoPath = $request->file('agency_logo')->store('agency_logos', 'public');
            }

            // Update agency details
            $agency->update([
                'name' => $request->agency_name,
                'email' => $request->agency_email,
                'logo' => $logoPath,
            ]);

            // Update admin details
            $adminUpdateData = [
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'phone' => $request->admin_phone,
            ];

            if ($request->new_password) {
                $adminUpdateData['password'] = Hash::make($request->new_password);
            }

            $admin->update($adminUpdateData);

            \DB::commit();

            // Log profile update
            \Log::info('Agency and admin profile updated', [
                'admin_id' => $admin->id,
                'admin_username' => $admin->username,
                'agency_id' => $agency->id,
                'agency_name' => $agency->name,
                'updated_fields' => [
                    'admin' => array_keys($adminUpdateData),
                    'agency' => ['name', 'email', 'logo']
                ],
                'ip_address' => $request->ip()
            ]);

            return back()->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error updating profile: ' . $e->getMessage());
            return back()->with('error', 'Error updating profile: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Check if agency admin is authenticated (for API endpoints)
     */
    public function checkAuth()
    {
        if (Auth::guard('agency')->check()) {
            $admin = Auth::guard('agency')->user();
            return response()->json([
                'authenticated' => true,
                'admin' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'username' => $admin->username,
                    'email' => $admin->email,
                    'agency' => [
                        'id' => $admin->agency->id,
                        'name' => $admin->agency->name,
                        'logo' => $admin->agency->logo_url,
                    ]
                ]
            ]);
        }

        return response()->json(['authenticated' => false], 401);
    }
}