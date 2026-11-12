<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesSiteLanguage;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Public "Businesses" page: search, filter by category, sort, paginate.
 * GET /businesses?q=&category=&sort=popular|rating|newest|name&page=
 */
class BusinessDirectoryController extends Controller
{
    use ResolvesSiteLanguage;

    private const SORTS = ['popular', 'rating', 'newest', 'name'];
    private const PER_PAGE = 12;
    private const REVIEWS_SHOWN = 10;

    public function index(Request $request)
    {
        $lang = $this->resolveSiteLanguage($request);

        // ?page must be a plain whole number >= 2; anything else (0, -1, abc, 1.5, page[]=, and 1 itself)
        // goes to the clean URL so there is one canonical address for page 1.
        // Infinite scroll (site.js) asks for the same URL with JSON: never redirect it, answer with cards instead.
        $wantsCards = $request->ajax() || $request->wantsJson();

        $rawPage = $request->query('page');
        $pageOk = is_string($rawPage) && ctype_digit($rawPage) && (int) $rawPage >= 1;
        if ($wantsCards && $rawPage !== null && !$pageOk) {
            return $this->cardsJson('', null);
        }
        if (!$wantsCards && $rawPage !== null && !($pageOk && (int) $rawPage > 1)) {
            return redirect()->to($request->fullUrlWithoutQuery('page'), 301);
        }
        // Cap absurd values (they are redirected to the last page below).
        $page = $rawPage === null ? 1 : min((int) $rawPage, 100000);

        $q = $request->query('q', '');
        $q = is_string($q) ? trim($q) : '';
        $q = mb_substr($q, 0, 80);
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'popular';

        // Only categories that actually contain businesses, most visited first.
        $categories = Category::whereHas('businesses')
            ->withCount('businesses')
            ->orderByDesc('category_visitors')
            ->orderBy('name')
            ->get(['id', 'name']);

        $categoryId = (int) $request->query('category');
        $activeCategory = $categoryId ? $categories->firstWhere('id', $categoryId) : null;

        $query = Business::query()
            ->with('category:id,name')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($activeCategory) {
            $query->where('category_id', $activeCategory->id);
        }

        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                  ->orWhere('services', 'like', $like)
                  ->orWhere('products', 'like', $like)
                  ->orWhere('description', 'like', $like)
                  ->orWhereHas('category', fn ($c) => $c->where('name', 'like', $like));
            });
        }

        match ($sort) {
            'rating' => $query->orderByDesc('reviews_avg_rating')->orderByDesc('reviews_count'),
            'newest' => $query->orderByDesc('created_at'),
            'name'   => $query->orderBy('name'),
            default  => $query->orderByDesc('visitors'),
        };
        $query->orderByDesc('id');

        $businesses = $query->paginate(self::PER_PAGE, ['*'], 'page', $page)->withQueryString();

        if ($wantsCards) {
            if ($businesses->currentPage() > $businesses->lastPage()) {
                return $this->cardsJson('', null);
            }
            $html = $businesses->getCollection()
                ->map(fn ($business) => view('partials.business-card', ['business' => $business])->render())
                ->implode("\n");

            return $this->cardsJson($html, $businesses->nextPageUrl(), $businesses->lastItem(), $businesses->total());
        }

        // Past the last page (old link, results shrank): go to the last page that has results.
        if ($businesses->currentPage() > $businesses->lastPage()) {
            $last = $businesses->lastPage();
            return redirect()->to($last > 1 ? $businesses->url($last) : $request->fullUrlWithoutQuery('page'));
        }

        $response = response()->view('businesses', [
            'lang'           => $lang,
            'activeNav'      => 'businesses',
            'businesses'     => $businesses,
            'categories'     => $categories,
            'activeCategory' => $activeCategory,
            'q'              => $q,
            'sort'           => $sort,
            'sorts'          => self::SORTS,
            'totalAll'       => $categories->sum('businesses_count'),
        ]);

        return $this->rememberSiteLanguage($response, $request, $lang)
            ->header('Vary', 'Accept, X-Requested-With');
    }

    /** Public web detail page: GET /businesses/{id} (the app share link /business/{id} is separate). */
    public function show(Request $request, int $id)
    {
        $lang = $this->resolveSiteLanguage($request);

        $business = Business::with(['category:id,name', 'businessImages'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->findOrFail($id);

        $this->countWebVisit($request, $business->id);

        // Latest reviews with the reviewer's name only (never phone/email/photo).
        $latestReviews = collect();
        $ratingBreakdown = array_fill_keys([5, 4, 3, 2, 1], 0);
        if ($business->reviews_count > 0) {
            $latestReviews = $business->reviews()
                ->with('user:id,name')
                ->latest()
                ->orderByDesc('id')
                ->limit(self::REVIEWS_SHOWN)
                ->get(['id', 'business_id', 'app_user_id', 'rating', 'comment', 'created_at']);

            // One grouped query for the 5..1 star bars.
            $counts = $business->reviews()
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

        $similar = collect();
        if ($business->category_id) {
            $similar = Business::query()
                ->with('category:id,name')
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->where('category_id', $business->category_id)
                ->whereKeyNot($business->id)
                ->orderByDesc('visitors')
                ->orderByDesc('id')
                ->limit(6)
                ->get();
        }

        $response = response()->view('business-detail', [
            'lang'      => $lang,
            'activeNav' => 'businesses',
            'business'  => $business,
            'similar'   => $similar,
            'latestReviews'   => $latestReviews,
            'ratingBreakdown' => $ratingBreakdown,
        ]);

        return $this->rememberSiteLanguage($response, $request, $lang);
    }

    /** Count one website view (separate from the app's `visitors`). Skips HEAD requests and bots. */
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
            $hasColumn ??= Schema::hasColumn('businesses', 'web_visitors');
            if ($hasColumn) {
                Business::whereKey($id)->increment('web_visitors');
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** JSON for infinite scroll: card <li> markup, the next page URL (null at the end), and counts. */
    private function cardsJson(string $html, ?string $next, ?int $shown = null, ?int $total = null)
    {
        return response()
            ->json(['html' => $html, 'next' => $next, 'shown' => $shown, 'total' => $total])
            ->header('Vary', 'Accept, X-Requested-With')
            ->header('Cache-Control', 'private, no-store');
    }
}
