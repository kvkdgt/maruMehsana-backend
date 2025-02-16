<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Category;
use App\Models\BusinessImages;
use Illuminate\Support\Facades\Storage;

class BusinessController extends Controller
{
    public function createView()
    {
        $categories = Category::get();
        return view('admin.business-form', compact('categories'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Optional additional images
            'category_id' => 'required|integer|exists:categories,id',
            'mobile' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'website' => 'nullable|url|max:255', // Optional website link
            'email' => 'nullable|email|max:255',
        ]);
        $thumbnailPath = $request->file('thumbnail')->store('business_thumbnails', 'public');
        $business = Business::create([
            'name' => $request->name,
            'description' => $request->description,
            'thumbnail' => $thumbnailPath,
            'category_id' => $request->category_id,
            'visitors' => 0, // Initially set to 0
            'mobile_no' => $request->mobile, // Optional
            'whatsapp_no' => $request->whatsapp, // Optional
            'website_url' => $request->website, // Optional
            'email_id' => $request->email
        ]);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('business_images', 'public');

                BusinessImages::create([
                    'image' => $imagePath,
                    'business_id' => $business->id,
                ]);
            }
        }
        return redirect()->route('admin.businesses')->with('success', 'Business created successfully!');
    }
    public function getBusinessById($id)
    {
        $business = Business::with('businessImages')->findOrFail($id);
        $categories = Category::get();
        return view('admin.business-form', compact('categories', 'business'));

        // return redirect()->route('admin.businesses')->with('success', 'Business deleted successfully!');
    }
    public function destroy($id)
    {
        $business = Business::findOrFail($id);

        // Check if the thumbnail exists and delete it
        if ($business->thumbnail) {
            $thumbnailPath = 'public/' . $business->thumbnail; // Path relative to storage

            if (Storage::exists($thumbnailPath)) {
                Storage::delete($thumbnailPath); // Delete the file from storage
            } else {
                // Log if the file doesn't exist
                dd("Thumbnail file not found: " . $thumbnailPath);
            }
        }

        // Delete the business record
        $business->delete();

        return redirect()->route('admin.businesses')->with('success', 'Business deleted successfully!');
    }

    public function deleteImage($id)
    {
        $businessImage = BusinessImages::findOrFail($id);

        if ($businessImage->deleteImage()) {
            return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Failed to delete the image.']);
    }

    public function update(Request $request)
    {
        $business = Business::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Optional thumbnail
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Optional additional images
            'category_id' => 'required|integer|exists:categories,id',
            'mobile' => 'nullable|string',
            'whatsapp' => 'nullable|string',
            'website' => 'nullable|url|max:255', // Optional website link
            'email' => 'nullable|email|max:255',
        ]);

        // Update basic fields
        $business->update([
            'name' => $request->name,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'mobile_no' => $request->mobile, // Optional
            'whatsapp_no' => $request->whatsapp, // Optional
            'website_url' => $request->website, // Optional
            'email_id' => $request->email, // Optional
        ]);

        // Update thumbnail if provided
        if ($request->hasFile('thumbnail')) {
            // Delete the old thumbnail if it exists
            if ($business->thumbnail && Storage::exists('public/' . $business->thumbnail)) {
                Storage::delete('public/' . $business->thumbnail);
            }

            // Store the new thumbnail
            $thumbnailPath = $request->file('thumbnail')->store('business_thumbnails', 'public');
            $business->update(['thumbnail' => $thumbnailPath]);
        }

        // Add new images if provided
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('business_images', 'public');

                BusinessImages::create([
                    'image' => $imagePath,
                    'business_id' => $business->id,
                ]);
            }
        }

        return redirect()->route('admin.businesses')->with('success', 'Business updated successfully!');
    }
}
