<?php

namespace App\Http\Controllers;

use App\Models\BannerAd;
use Illuminate\Http\Request;

class BannerAdController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'link' => 'nullable|url',
            'status' => 'required|boolean',
            'business_id' => 'nullable|exists:businesses,id',
            'tourist_place_id' => 'nullable|exists:tourist_places,id',
        ]);

        $imagePath = $request->file('image')->store('banner_ads', 'public');

        BannerAd::create([
            'title' => $request->title,
            'image' => $imagePath,
            'link' => $request->link,
            'status' => $request->status,
            'business_id' => $request->business_id,
            'tourist_place_id' => $request->tourist_place_id,
            'touch' => 0
        ]);

        return redirect()->route('admin.banner-ads')->with('success', 'Banner Ad added successfully.');
    }

    public function destroy(BannerAd $bannerAd)
    {
        if ($bannerAd->image) {
            \Storage::disk('public')->delete($bannerAd->image);
        }
        $bannerAd->delete();

        return redirect()->route('admin.banner-ads')->with('success', 'Banner Ad deleted successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $bannerAd = BannerAd::findOrFail($id);
        $bannerAd->status = $request->status;
        $bannerAd->save();
    
        return response()->json(['success' => true, 'status' => $bannerAd->status]);
    }

    public function getActiveBanners()
    {
        $activeBanners = BannerAd::where('status', 1)->get();

        return response()->json([
            'success' => true,
            'message' => 'Active banner ads retrieved successfully.',
            'data' => $activeBanners,
        ], 200);
    }

    public function incrementTouch($id)
    {
        $banner = BannerAd::find($id);

        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'Banner ad not found.',
            ], 404);
        }

        // Increment touch count
        $banner->increment('touch');

        return response()->json([
            'success' => true,
            'message' => 'Touch count incremented successfully.',
            'data' => [
                'id' => $banner->id,
                'title' => $banner->title,
                'touch' => $banner->touch,
            ],
        ], 200);
    }
    
}
