<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Models\NewsArticle;
use App\Models\TouristPlace;
use App\Models\Fact;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    public function index()
    {
        $categories = Category::limit(8)->get();
        $featuredBusinesses = Business::with('category')->latest()->limit(6)->get();
        $latestNews = NewsArticle::with('agency')->where('is_active', true)->latest()->limit(3)->get();
        $touristPlaces = TouristPlace::latest()->limit(4)->get();
        $facts = Fact::latest()->limit(5)->get();

        return view('landing', compact(
            'categories',
            'featuredBusinesses',
            'latestNews',
            'touristPlaces',
            'facts'
        ));
    }
}
