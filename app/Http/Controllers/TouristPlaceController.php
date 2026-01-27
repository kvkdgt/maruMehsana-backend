<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TouristPlace;
use App\Models\TouristPlaceImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TouristPlaceController extends Controller
{
    // Create a new tourist place
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        $thumbnailPath = $request->file('thumbnail')->store('tourist_thumbnails', 'public');
    
        $touristPlace = TouristPlace::create([
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'thumbnail' => $thumbnailPath,
            'created_by' => Auth::id(),
            'visitors' => 0,
        ]);
    
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('tourist_images', 'public');
                TouristPlaceImage::create([
                    'image' => $imagePath,
                    'tourist_place_id' => $touristPlace->id,
                ]);
            }
        }
    
        return response()->json(['message' => 'Tourist place created successfully', 'data' => $touristPlace], 201);
    }
    

    // Get all tourist places
    // public function index(Request $request)
    // {
    //     $pageNo = $request->input('pageNo', 1);
    //     $limit = $request->input('limit', 10);
    
    //     $places = TouristPlace::with('placeImages')
    //         ->paginate($limit, ['*'], 'page', $pageNo);
    
    //     return response()->json([
    //         'message' => 'Tourist places retrieved successfully',
    //         'data' => $places
    //     ], 200);
    // }

   public function index(Request $request)
{
    $pageNo = $request->input('pageNo', 1);
    $limit = $request->input('limit', 10);
    $sortBy = $request->input('sortBy'); // Options: 'nearest_first', 'nearest_last', 'most_viewed', 'least_viewed'
    $userLatitude = $request->input('latitude');
    $userLongitude = $request->input('longitude');

    $query = TouristPlace::with('placeImages');

    // Always calculate distance if user coordinates are provided
    if ($userLatitude && $userLongitude) {
        $query->selectRaw("
            *,
            (6371 * acos(
                cos(radians(?)) * 
                cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * 
                sin(radians(latitude))
            )) AS distance
        ", [$userLatitude, $userLongitude, $userLatitude]);
    } else {
        // If no user coordinates, set distance as -1
        $query->selectRaw("*, -1 AS distance");
    }

    // Apply sorting based on the sortBy parameter
    if ($sortBy) {
        switch ($sortBy) {
            case 'nearest_first':
                // Validate that user coordinates are provided for distance sorting
                if (!$userLatitude || !$userLongitude) {
                    return response()->json([
                        'message' => 'User latitude and longitude are required for distance sorting',
                        'data' => null
                    ], 400);
                }
                $query->orderBy('distance', 'asc');
                break;

            case 'nearest_last':
                // Validate that user coordinates are provided for distance sorting
                if (!$userLatitude || !$userLongitude) {
                    return response()->json([
                        'message' => 'User latitude and longitude are required for distance sorting',
                        'data' => null
                    ], 400);
                }
                $query->orderBy('distance', 'desc');
                break;

            case 'most_viewed':
                $query->orderBy('visitors', 'desc');
                break;

            case 'least_viewed':
                $query->orderBy('visitors', 'asc');
                break;

            default:
                // If invalid sortBy parameter, continue without sorting
                break;
        }
    }

    $places = $query->paginate($limit, ['*'], 'page', $pageNo);
    
    // Add average rating to each place
    foreach ($places as $place) {
        $avgRating = $place->reviews()->avg('rating');
        $place->avg_rating = $avgRating ? round($avgRating, 1) : 0;
    }

    return response()->json([
        'message' => 'Tourist places retrieved successfully',
        'data' => $places,
        'sorting_applied' => $sortBy ?? 'none'
    ], 200);
}

    // Get a single tourist place by ID
    public function show($id)
    {
        $place = TouristPlace::with('placeImages')
            ->withCount('reviews')
            ->findOrFail($id);
    
        // Increment visitor count
        $place->increment('visitors');
        
        // Calculate average rating
        $avgRating = $place->reviews()->avg('rating');
        $place->avg_rating = $avgRating ? round($avgRating, 1) : 0;
        
        // Get recent 5 reviews
        $recentReviews = $place->reviews()
            ->with(['user:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $place->recent_reviews = $recentReviews;
    
        return response()->json([
            'message' => 'Tourist place retrieved successfully',
            'data' => $place
        ], 200);
    }
    

    // Update a tourist place
    public function update(Request $request, $id)
    {
        $place = TouristPlace::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $place->update([
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'updated_by' => Auth::id(),
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($place->thumbnail && Storage::exists('public/' . $place->thumbnail)) {
                Storage::delete('public/' . $place->thumbnail);
            }
            $thumbnailPath = $request->file('thumbnail')->store('tourist_thumbnails', 'public');
            $place->update(['thumbnail' => $thumbnailPath]);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('tourist_images', 'public');
                TouristPlaceImage::create([
                    'image' => $imagePath,
                    'tourist_place_id' => $place->id,
                ]);
            }
        }

        return response()->json(['message' => 'Tourist place updated successfully', 'data' => $place], 200);
    }

    // Delete a tourist place
    public function destroy($id)
    {
        $place = TouristPlace::findOrFail($id);

        if ($place->thumbnail && Storage::exists('public/' . $place->thumbnail)) {
            Storage::delete('public/' . $place->thumbnail);
        }

        $place->placeImages()->delete();
        $place->delete();

        return response()->json(['message' => 'Tourist place deleted successfully'], 200);
    }

    // Get tourist places within a radius (bonus method for location-based queries)
    public function getNearbyPlaces(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:1|max:100', // radius in kilometers
        ]);

        $lat = $request->latitude;
        $lng = $request->longitude;
        $radius = $request->radius ?? 10; // default 10km

        // Using Haversine formula to calculate distance
        $places = TouristPlace::selectRaw("
                *,
                (6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude)))) AS distance
            ")
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->with('placeImages')
            ->get();

        return response()->json([
            'message' => 'Nearby tourist places retrieved successfully',
            'data' => $places
        ], 200);
    }
}