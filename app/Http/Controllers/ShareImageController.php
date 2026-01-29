<?php

namespace App\Http\Controllers;

use App\Models\TouristPlace;
use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ShareImageController extends Controller
{
    public function sharePlace($id)
    {
        $place = TouristPlace::findOrFail($id);
        $imagePath = storage_path('app/public/' . $place->thumbnail);
        
        if (!file_exists($imagePath)) {
            return response()->json(['error' => 'Image not found at ' . $imagePath], 404);
        }

        return response()->file($imagePath);
    }

    public function shareNews($id)
    {
        $news = NewsArticle::findOrFail($id);
        // Assuming news images are in public/news or similar, but following news article model
        $imagePath = storage_path('app/public/news/' . $news->image);
        
        if (!file_exists($imagePath)) {
            // Fallback if not in news subfolder
            $imagePath = storage_path('app/public/' . $news->image);
            if (!file_exists($imagePath)) {
                return response()->json(['error' => 'News image not found'], 404);
            }
        }

        return response()->file($imagePath);
    }
}
