<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesSiteLanguage;
use App\Models\TouristPlace;
use Illuminate\Http\Request;

/**
 * Public web detail page for a tourist place: GET /places/{id}
 * (the app share link /place/{id} is separate and handled by ShareController).
 */
class PlaceDetailController extends Controller
{
    use ResolvesSiteLanguage;

    private const REVIEWS_SHOWN = 10;
    private const MORE_PLACES = 6;

    public function show(Request $request, int $id)
    {
        $lang = $this->resolveSiteLanguage($request);

        $place = TouristPlace::with('placeImages')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);

        // Latest reviews with the reviewer's name only (never phone/email/photo).
        $latestReviews = collect();
        $ratingBreakdown = array_fill_keys([5, 4, 3, 2, 1], 0);
        if ($place->reviews_count > 0) {
            $latestReviews = $place->reviews()
                ->with('user:id,name')
                ->latest()
                ->orderByDesc('id')
                ->limit(self::REVIEWS_SHOWN)
                ->get(['id', 'tourist_place_id', 'app_user_id', 'rating', 'comment', 'created_at']);

            // One grouped query for the 5..1 star bars.
            $counts = $place->reviews()
                ->selectRaw('rating, COUNT(*) as total')
                ->groupBy('rating')
                ->pluck('total', 'rating');
            foreach ($counts as $star => $total) {
                $star = (int) round((float) $star);
                if (isset($ratingBreakdown[$star])) {
                    $ratingBreakdown[$star] += (int) $total;
                }
            }
        }

        // Other popular places for the "More places" section.
        $morePlaces = TouristPlace::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->whereKeyNot($place->id)
            ->orderByRaw('(visitors + web_visitors) desc')
            ->orderByDesc('id')
            ->limit(self::MORE_PLACES)
            ->get(['id', 'name', 'thumbnail', 'location', 'visitors', 'web_visitors']);

        $response = response()->view('place-detail', [
            'lang'            => $lang,
            'activeNav'       => 'places',
            'place'           => $place,
            'morePlaces'      => $morePlaces,
            'latestReviews'   => $latestReviews,
            'ratingBreakdown' => $ratingBreakdown,
        ]);

        return $this->rememberSiteLanguage($response, $request, $lang);
    }
}
