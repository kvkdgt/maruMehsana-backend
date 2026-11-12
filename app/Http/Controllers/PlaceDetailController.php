<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesSiteLanguage;
use App\Models\TouristPlace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

        $this->countWebVisit($request, $place->id);

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
            ->orderByDesc('visitors')
            ->orderByDesc('id')
            ->limit(self::MORE_PLACES)
            ->get(['id', 'name', 'thumbnail', 'location', 'visitors']);

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

    /**
     * Count one website view, kept apart from the app's `visitors` like businesses do.
     * Only runs when tourist_places has a `web_visitors` column. Skips HEAD requests and bots.
     */
    private function countWebVisit(Request $request, int $id): void
    {
        static $hasColumn = null;

        if ($request->isMethod('HEAD')) {
            return;
        }
        $ua = (string) $request->userAgent();
        if ($ua === '' || preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|fetch|monitor|curl|wget|python|java\/|httpclient|headless|lighthouse/i', $ua)) {
            return;
        }
        try {
            $hasColumn ??= Schema::hasColumn('tourist_places', 'web_visitors');
            if ($hasColumn) {
                TouristPlace::whereKey($id)->increment('web_visitors');
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
