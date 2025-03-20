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


class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
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
    public function dashboard()
    {
        $totalCategories = Category::count();
        $totalBusinesses = Business::count();

        return view('admin.dashboard', compact('totalCategories','totalBusinesses')); // Create a simple admin dashboard view
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
        $categories = $categoriesQuery->get();
    
        return view('admin.categories', compact('categories'));
    }

    public function businesses(Request $request){
        $search = $request->get('search');
        $sortBy = $request->get('sort_by');
        
        // Build the query
        $businessesQuery = Business::with('category');
    
        // Apply search filter
        if ($search) {
            $businessesQuery->where('name', 'LIKE', "%$search%")
                            ->orWhere('description', 'LIKE', "%$search%");
        }
        
        // Apply sorting by visitors
        if ($sortBy == 'highest') {
            $businessesQuery->orderBy('visitors', 'desc');
        } elseif ($sortBy == 'lowest') {
            $businessesQuery->orderBy('visitors', 'asc');
        }
    
        $businesses = $businessesQuery->get();
        return view("admin.businesses", compact('businesses'));
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
    
        // Apply sorting by visitors
        if ($sortBy == 'highest') {
            $touristPlacesQuery->orderBy('visitors', 'desc');
        } elseif ($sortBy == 'lowest') {
            $touristPlacesQuery->orderBy('visitors', 'asc');
        }
    
        $tourist_places = $touristPlacesQuery->get();
    
        return view("admin.tourist-places", compact('tourist_places'));
    }
    

    public function facts(Request $request){
        $search = $request->get('search');
        $factsQuery = Fact::query();
        if ($search) {
            $factsQuery->where('fact', 'LIKE', "%$search%");
        }
        $facts = $factsQuery->get();
        return view("admin.facts", compact('facts'));
    }
}
