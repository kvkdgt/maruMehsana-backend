<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsAgency;
use App\Models\AgencyAdmin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class NewsAgencyController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsAgency::with('admin');
        
        // Search functionality
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }
        
        // Status filter - working with your boolean status
        if ($request->status !== null && $request->status !== '') {
            if ($request->status === 'active') {
                $query->where('status', true);
            } elseif ($request->status === 'inactive') {
                $query->where('status', false);
            }
        }
        
        $agencies = $query->paginate(10);
        
        return view('admin.news-agencies.index', compact('agencies'));
    }

    public function create()
    {
        return view('admin.news-agencies.form');
    }

    public function store(Request $request)
    {
        // Debug: Log the request data
        \Log::info('News Agency Store Request:', $request->all());
        
        $request->validate([
            'agency_name' => 'required|string|max:255',
            'agency_email' => 'required|email|unique:news_agencies,email',
            'agency_username' => 'required|string|unique:news_agencies,username',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:agency_admins,email',
            'admin_username' => 'required|string|unique:agency_admins,username',
            'admin_password' => 'required|string|min:6',
            'admin_phone' => 'nullable|string',
        ], [
            'agency_name.required' => 'Agency name is required.',
            'agency_email.required' => 'Agency email is required.',
            'agency_email.unique' => 'This email is already taken.',
            'agency_username.required' => 'Agency username is required.',
            'agency_username.unique' => 'This username is already taken.',
            'admin_name.required' => 'Administrator name is required.',
            'admin_email.required' => 'Administrator email is required.',
            'admin_email.unique' => 'This admin email is already taken.',
            'admin_username.required' => 'Administrator username is required.',
            'admin_username.unique' => 'This admin username is already taken.',
            'admin_password.required' => 'Password is required.',
            'admin_password.min' => 'Password must be at least 6 characters.',
        ]);

        DB::beginTransaction();
        try {
            // Handle logo upload with auto folder creation
            $logoPath = null;
            if ($request->hasFile('logo')) {
                // Ensure the storage directory exists
                $this->ensureDirectoryExists('public/agency_logos');
                
                $logoPath = $request->file('logo')->store('agency_logos', 'public');
                \Log::info('Logo uploaded to: ' . $logoPath);
            }

            // Create News Agency - using boolean status
            $agency = NewsAgency::create([
                'name' => $request->agency_name,
                'email' => $request->agency_email,
                'username' => $request->agency_username,
                'logo' => $logoPath,
                'status' => true // boolean true for active
            ]);

            \Log::info('Agency created with ID: ' . $agency->id);

            // Create Agency Admin - using boolean status
            $admin = AgencyAdmin::create([
                'agency_id' => $agency->id,
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'username' => $request->admin_username,
                'password' => Hash::make($request->admin_password),
                'phone' => $request->admin_phone,
                'status' => true // boolean true for active
            ]);

            \Log::info('Admin created with ID: ' . $admin->id);

            DB::commit();
            return redirect()->route('admin.news-agencies')->with('success', 'News Agency and Admin created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error creating news agency: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $agency = NewsAgency::with('admin')->findOrFail($id);
        return view('admin.news-agencies.form', compact('agency'));
    }

    public function update(Request $request, $id)
    {
        $agency = NewsAgency::findOrFail($id);
        
        $request->validate([
            'agency_name' => 'required|string|max:255',
            'agency_email' => 'required|email|unique:news_agencies,email,' . $id,
            'agency_username' => 'required|string|unique:news_agencies,username,' . $id,
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|unique:agency_admins,email,' . $agency->admin->id,
            'admin_username' => 'required|string|unique:agency_admins,username,' . $agency->admin->id,
            'admin_password' => 'nullable|string|min:6',
            'admin_phone' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Handle logo upload with auto folder creation
            $logoPath = $agency->logo;
            if ($request->hasFile('logo')) {
                // Ensure the storage directory exists
                $this->ensureDirectoryExists('public/agency_logos');
                
                // Delete old logo
                if ($agency->logo && Storage::exists('public/' . $agency->logo)) {
                    Storage::delete('public/' . $agency->logo);
                }
                $logoPath = $request->file('logo')->store('agency_logos', 'public');
            }

            // Update News Agency
            $agency->update([
                'name' => $request->agency_name,
                'email' => $request->agency_email,
                'username' => $request->agency_username,
                'logo' => $logoPath,
            ]);

            // Update Agency Admin
            $adminData = [
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'username' => $request->admin_username,
                'phone' => $request->admin_phone,
            ];
            
            if ($request->admin_password) {
                $adminData['password'] = Hash::make($request->admin_password);
            }

            $agency->admin->update($adminData);

            DB::commit();
            return redirect()->route('admin.news-agencies')->with('success', 'News Agency updated successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error updating news agency: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $agency = NewsAgency::findOrFail($id);
        
        // Delete logo if exists
        if ($agency->logo && Storage::exists('public/' . $agency->logo)) {
            Storage::delete('public/' . $agency->logo);
        }
        
        $agency->delete();
        
        return redirect()->route('admin.news-agencies')->with('success', 'News Agency deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $agency = NewsAgency::findOrFail($id);
        $newStatus = !$agency->status; // Toggle boolean value
        
        $agency->update(['status' => $newStatus]);
        if ($agency->admin) {
            $agency->admin->update(['status' => $newStatus]);
        }
        
        return response()->json([
            'success' => true,
            'status' => $newStatus ? 'active' : 'inactive',
            'message' => 'Status updated successfully!'
        ]);
    }

    /**
     * Ensure the specified directory exists, create it if it doesn't
     *
     * @param string $path
     * @return void
     */
    private function ensureDirectoryExists($path)
    {
        $fullPath = storage_path('app/' . $path);
        
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
            \Log::info('Created directory: ' . $fullPath);
        }
        
        // Also ensure the symbolic link exists for public access
        if (strpos($path, 'public/') === 0) {
            $publicPath = public_path('storage/' . str_replace('public/', '', $path));
            if (!file_exists($publicPath)) {
                // Create the public storage directory structure
                $publicDir = dirname($publicPath);
                if (!file_exists($publicDir)) {
                    mkdir($publicDir, 0755, true);
                }
                
                // Create symbolic link if it doesn't exist
                if (!file_exists(public_path('storage'))) {
                    \Artisan::call('storage:link');
                    \Log::info('Created storage link');
                }
            }
        }
    }
}