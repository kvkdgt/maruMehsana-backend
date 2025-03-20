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
            'location' => 'nullable|string', // Added location as an optional field
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        $thumbnailPath = $request->file('thumbnail')->store('tourist_thumbnails', 'public');
    
        $touristPlace = TouristPlace::create([
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location, // Saving the location
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
    public function index(Request $request)
    {
        $pageNo = $request->input('pageNo', 1); // Default page number is 1
        $limit = $request->input('limit', 10);  // Default limit per page is 10
    
        // Paginate the results
        $places = TouristPlace::with('placeImages')
            ->paginate($limit, ['*'], 'page', $pageNo);
    
     
    
        return response()->json([
            'message' => 'Tourist places retrieved successfully',
            'data' => $places
        ], 200);
    }

    // Get a single tourist place by ID
    public function show($id)
    {
        $place = TouristPlace::with('placeImages')->findOrFail($id);
    
        // Increment visitor count
        $place->increment('visitors');
    
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
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'location' => 'required|string|max:255',
        ]);

        $place->update([
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
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
}
