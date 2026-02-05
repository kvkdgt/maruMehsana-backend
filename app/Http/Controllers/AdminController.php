<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Business;
use App\Models\Fact;
use App\Models\TouristPlace;
use App\Models\BusinessEnquiry;
use App\Models\BannerAd;
use Illuminate\Support\Collection;
use App\Models\AppUser;
use App\Models\Notification;
use App\Models\NotificationLog;
use Carbon\Carbon;


class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function marketing()
    {
        $scheduledCount = Notification::where('scheduled_at', '>', now())->count();
        $activeBannerCount = \App\Models\BannerAd::where('status', 1)->count();


        return view('admin.marketing',compact('scheduledCount','activeBannerCount'));
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Attempt login with credentials
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->is_admin) {
                // Admin login success
                return redirect('/admin/dashboard');
            } else {
                // Logout non-admin users
                Auth::logout();
                return back()->withErrors(['Invalid credentials or not an admin.']);
            }
        }

        // Invalid credentials
        return back()->withErrors(['Invalid credentials or not an admin.']);
    }

    // Admin dashboard
    public function dashboardOld()
    {
        $totalCategories = Category::count();
        $totalBusinesses = Business::count();

        return view('admin.dashboard', compact('totalCategories','totalBusinesses')); // Create a simple admin dashboard view
    }

    public function dashboard()
{
    // Core model counts
    $totalCategories = Category::count();
    $totalBusinesses = Business::count();
    $totalAppUsers = AppUser::count();
    $totalTouristPlaces = TouristPlace::count();
    
    // Visitor statistics
    $totalCategoryVisitors = Category::sum('category_visitors');
    $totalBusinessVisitors = Business::sum('visitors');
    $totalTouristVisitors = TouristPlace::sum('visitors');
    
    // App users statistics
    $activeUsers = AppUser::where('is_login', true)->count();
    $newUsersToday = AppUser::whereDate('created_at', today())->count();
    $recentUsers = AppUser::orderBy('created_at', 'desc')->take(5)->get();
    
    // Business enquiries
    $pendingEnquiries = BusinessEnquiry::where('status', 'Pending')->count();
    $recentEnquiries = BusinessEnquiry::orderBy('created_at', 'desc')->take(3)->get();
    
    // Banner ads
    $activeBannerAds = BannerAd::where('status', 1)->take(4)->get();
    
    // Random facts
    $randomFacts = Fact::inRandomOrder()->take(5)->get();
    $notificationStats = [
        'total' => Notification::count(),
        'sent' => Notification::where('is_sent', true)->count(),
        'scheduled' => Notification::where('is_sent', false)->whereNotNull('scheduled_at')->count(),
        'logs' => [
            'total' => NotificationLog::count(),
            'delivered' => NotificationLog::where('status', 'delivered')->count(),
            'sent' => NotificationLog::where('status', 'sent')->count(),
            'failed' => NotificationLog::where('status', 'failed')->count(),
        ],
    ];
    
    // Recent notifications
    $recentNotifications = Notification::where('is_sent', true)
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
        $startDate = Carbon::now()->subDays(7);
        $weeklyNotifications = Notification::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        $chartData = [
            'labels' => $weeklyNotifications->pluck('date')->toArray(),
            'data' => $weeklyNotifications->pluck('count')->toArray(),
        ];
    
    return view('admin.dashboard', compact(
        'totalCategories',
        'totalBusinesses',
        'totalAppUsers',
        'totalTouristPlaces',
        'totalCategoryVisitors',
        'totalBusinessVisitors',
        'totalTouristVisitors',
        'activeUsers',
        'newUsersToday',
        'recentUsers',
        'pendingEnquiries',
        'recentEnquiries',
        'activeBannerAds',
        'randomFacts',
        'notificationStats', 'recentNotifications', 'chartData'
    ));
}

    public function signup(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        // Create the admin user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash the password
            'is_admin' => true, // Set is_admin flag
        ]);

        return response()->json([
            'message' => 'Admin account created successfully',
            'user' => $user,
        ], 201);
    }

    public function categories(Request $request)
    {
        $search = $request->get('search');
        $sortBy = $request->get('sort_by');
        
        // Build the query
        $categoriesQuery = Category::query();
        
        // Apply search filter
        if ($search) {
            $categoriesQuery->where('name', 'LIKE', "%$search%")
                            ->orWhere('description', 'LIKE', "%$search%");
        }
        
        // Apply sorting by visitors
        if ($sortBy == 'highest') {
            $categoriesQuery->orderBy('category_visitors', 'desc');
        } elseif ($sortBy == 'lowest') {
            $categoriesQuery->orderBy('category_visitors', 'asc');
        }
    
        // Get categories
        $categories = $categoriesQuery->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    public function businesses(Request $request){
        $search = $request->get('search');
        $sortBy = $request->get('sort_by');
        $categoryId = $request->get('category_id');
        
        // Build the query
        $businessesQuery = Business::with('category');
    
        // Apply search filter
        if ($search) {
            $businessesQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('description', 'LIKE', "%$search%")
                  ->orWhere('products', 'LIKE', "%$search%")
                  ->orWhere('services', 'LIKE', "%$search%");
            });
        }

        // Apply category filter
        if ($categoryId) {
            $businessesQuery->where('category_id', $categoryId);
        }
        
        // Apply sorting
        if ($sortBy == 'highest') {
            $businessesQuery->orderBy('visitors', 'desc');
        } elseif ($sortBy == 'lowest') {
            $businessesQuery->orderBy('visitors', 'asc');
        } elseif ($sortBy == 'newest') {
            $businessesQuery->orderBy('created_at', 'desc');
        } elseif ($sortBy == 'oldest') {
            $businessesQuery->orderBy('created_at', 'asc');
        } else {
            $businessesQuery->orderBy('created_at', 'desc');
        }
    
        $businesses = $businessesQuery->paginate(10);
        $categories = Category::all();
        return view("admin.businesses", compact('businesses', 'categories'));
    }

    public function touristPlaces(Request $request)
    {
        $search = $request->get('search');
        $sortBy = $request->get('sort_by');
    
        // Build the query
        $touristPlacesQuery = TouristPlace::query();
    
        // Apply search filter
        if ($search) {
            $touristPlacesQuery->where('name', 'LIKE', "%$search%")
                               ->orWhere('description', 'LIKE', "%$search%");
        }
    
        // Apply sorting
        if ($sortBy == 'highest') {
            $touristPlacesQuery->orderBy('visitors', 'desc');
        } elseif ($sortBy == 'lowest') {
            $touristPlacesQuery->orderBy('visitors', 'asc');
        } elseif ($sortBy == 'newest') {
            $touristPlacesQuery->orderBy('created_at', 'desc');
        } elseif ($sortBy == 'oldest') {
            $touristPlacesQuery->orderBy('created_at', 'asc');
        } else {
            $touristPlacesQuery->orderBy('created_at', 'desc');
        }
    
        $tourist_places = $touristPlacesQuery->paginate(10);
    
        return view("admin.tourist-places", compact('tourist_places'));
    }

    public function BusinessEnquiry(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $pageSize = $request->get('size', 10);
    
        // Build the query
        $businessEnquiryQuery = BusinessEnquiry::query();
    
        // Apply search filter
        if ($search) {
            $businessEnquiryQuery->where(function($q) use ($search) {
                $q->where('business_name', 'LIKE', "%$search%")
                  ->orWhere('owner_name', 'LIKE', "%$search%")
                  ->orWhere('mobile_no', 'LIKE', "%$search%")
                  ->orWhere('whatsapp_no', 'LIKE', "%$search%");
            });
        }

        // Apply status filter if provided
        if ($status) {
            $businessEnquiryQuery->where('status', $status);
        }

        // Apply date range filter
        if ($startDate) {
            $businessEnquiryQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $businessEnquiryQuery->whereDate('created_at', '<=', $endDate);
        }
    
        $business_enquiries = $businessEnquiryQuery->orderBy('created_at', 'desc')->paginate($pageSize);
    
        return view("admin.business-enquiries", compact('business_enquiries'));
    }
    
    
    

    public function facts(Request $request){
        $search = $request->get('search');
        $factsQuery = Fact::query();
        if ($search) {
            $factsQuery->where('fact', 'LIKE', "%$search%");
        }
        $facts = $factsQuery->paginate(10);
        return view("admin.facts", compact('facts'));
    }

    public function bannerAds(Request $request){
        $search = $request->search;
        $bannerAds = BannerAd::when($search, function ($query, $search) {
            return $query->where('title', 'like', "%$search%");
        })->paginate(10);
        
        // Fetch businesses
        $businesses = \App\Models\Business::select('id', 'name', 'thumbnail', 'description')
                        ->orderBy('created_at', 'desc')
                        ->get();

        // Fetch tourist places
        $touristPlaces = \App\Models\TouristPlace::select('id', 'name', 'thumbnail', 'description')
                        ->orderBy('created_at', 'desc')
                        ->get();
                        
        return view("admin.bannerAds", compact('bannerAds', 'businesses', 'touristPlaces'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['error' => 'Search query is required'], 400);
        }

        // Search in Categories
        $categories = Category::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->select('id', 'name')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'type' => 'Category',
            ]);

        // Search in Businesses
        $businesses = Business::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhere('products', 'LIKE', "%{$query}%")
            ->orWhere('services', 'LIKE', "%{$query}%")
            ->select('id', 'name')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'type' => 'Business',
            ]);

        // Search in Tourist Places
        $places = TouristPlace::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhere('location', 'LIKE', "%{$query}%")
            ->select('id', 'name')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'type' => 'Tourist Place',
            ]);

        // Merge all results into a collection
        $results = new Collection();
        $results = $results->merge($categories);
        $results = $results->merge($businesses);
        $results = $results->merge($places);

        // Shuffle to mix up results randomly
        $shuffledResults = $results->shuffle();

        // Limit to 10 most relevant results
        $finalResults = $shuffledResults->take(10)->values();

        return response()->json($finalResults);
    }
    public function appUsers(Request $request) {
        $search = $request->get('search');
        $sortBy = $request->get('sort_by');
        $isLogin = $request->get('is_login');
        $hasFcm = $request->get('has_fcm');

        $usersQuery = AppUser::query();

        if ($search) {
            $usersQuery->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%");
            });
        }

        if ($isLogin !== null && $isLogin !== '') {
            $usersQuery->where('is_login', $isLogin);
        }

        if ($hasFcm === 'yes') {
            $usersQuery->whereNotNull('fcm_tokens')->whereRaw('JSON_LENGTH(fcm_tokens) > 0');
        } elseif ($hasFcm === 'no') {
            $usersQuery->where(function($q) {
                $q->whereNull('fcm_tokens')->orWhereRaw('JSON_LENGTH(fcm_tokens) = 0');
            });
        }

        if ($sortBy == 'newest') {
            $usersQuery->orderBy('created_at', 'desc');
        } elseif ($sortBy == 'oldest') {
            $usersQuery->orderBy('created_at', 'asc');
        } else {
            $usersQuery->orderBy('created_at', 'desc');
        }

        $appUsers = $usersQuery->paginate(10);

        return view('admin.app-users', compact('appUsers'));
    }

    public function deleteAppUser($id) {
        $user = AppUser::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully');
    }
}
