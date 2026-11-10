<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesSiteLanguage;
use App\Models\Business;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * Public "Businesses" page: search, filter by category, sort, paginate.
 * GET /businesses?q=&category=&sort=popular|rating|newest|name&page=
 */
class BusinessDirectoryController extends Controller
{
    use ResolvesSiteLanguage;

    private const SORTS = ['popular', 'rating', 'newest', 'name'];
    private const PER_PAGE = 12;

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

    /** JSON for infinite scroll: card <li> markup, the next page URL (null at the end), and counts. */
    private function cardsJson(string $html, ?string $next, ?int $shown = null, ?int $total = null)
    {
        return response()
            ->json(['html' => $html, 'next' => $next, 'shown' => $shown, 'total' => $total])
            ->header('Vary', 'Accept, X-Requested-With')
            ->header('Cache-Control', 'private, no-store');
    }
}
