<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Models\Fact;
use App\Models\JobVacancy;
use App\Models\NewsArticle;
use App\Models\TouristPlace;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    private const LOCALES = ['gu', 'en'];
    private const COOKIE = 'mm_lang';

    public function index(Request $request)
    {
        // Language: ?lang=gu|en wins (and is remembered in a cookie), then the cookie, then Gujarati.
        $fromQuery = $request->query('lang');
        $lang = in_array($fromQuery, self::LOCALES, true)
            ? $fromQuery
            : $request->cookie(self::COOKIE);
        if (!in_array($lang, self::LOCALES, true)) {
            $lang = 'gu';
        }
        app()->setLocale($lang);
        Carbon::setLocale($lang);

        // Top businesses: most visited first, with real review stats (null when unreviewed).
        $topBusinesses = Business::with('category:id,name')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('visitors')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $latestNews = NewsArticle::with('agency:id,name')
            ->where('is_active', true)
            ->latest()
            ->limit(3)
            ->get();

        $popularPlaces = TouristPlace::orderByDesc('visitors')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $latestJobs = JobVacancy::active()->latest()->limit(3)->get();

        $facts = Fact::inRandomOrder()->limit(5)->get();

        // Popular-search chips: the most visited categories that actually contain businesses.
        $chipCategories = Category::whereHas('businesses')
            ->orderByDesc('category_visitors')
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name']);

        $response = response()->view('landing', compact(
            'lang',
            'topBusinesses',
            'latestNews',
            'popularPlaces',
            'latestJobs',
            'facts',
            'chipCategories'
        ));

        if (in_array($fromQuery, self::LOCALES, true)) {
            $response->cookie(self::COOKIE, $lang, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'Lax');
        }

        return $response;
    }
}
