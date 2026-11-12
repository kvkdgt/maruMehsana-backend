<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesSiteLanguage;
use App\Models\TouristPlace;
use Illuminate\Http\Request;

/**
 * Public "Tourist places" page: search, sort, paginate (with infinite scroll).
 * GET /places?q=&sort=popular|rating|newest|name&page=
 * The detail page (/places/{id}) lives in PlaceDetailController.
 */
class PlaceDirectoryController extends Controller
{
    use ResolvesSiteLanguage;

    private const SORTS = ['popular', 'rating', 'newest', 'name'];
    private const PER_PAGE = 12;

    public function index(Request $request)
    {
        $lang = $this->resolveSiteLanguage($request);

        // Same page rules as /businesses: ?page must be a whole number >= 2, otherwise the clean URL.
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
        $page = $rawPage === null ? 1 : min((int) $rawPage, 100000);

        $q = $request->query('q', '');
        $q = is_string($q) ? trim($q) : '';
        $q = mb_substr($q, 0, 80);
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'popular';

        $query = TouristPlace::query()
            ->select(['id', 'name', 'description', 'thumbnail', 'location', 'visitors', 'created_at'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                  ->orWhere('location', 'like', $like)
                  ->orWhere('description', 'like', $like);
            });
        }

        match ($sort) {
            'rating' => $query->orderByDesc('reviews_avg_rating')->orderByDesc('reviews_count'),
            'newest' => $query->orderByDesc('created_at'),
            'name'   => $query->orderBy('name'),
            default  => $query->orderByDesc('visitors'),
        };
        $query->orderByDesc('id');

        $places = $query->paginate(self::PER_PAGE, ['*'], 'page', $page)->withQueryString();

        if ($wantsCards) {
            if ($places->currentPage() > $places->lastPage()) {
                return $this->cardsJson('', null);
            }
            $html = $places->getCollection()
                ->map(fn ($place) => view('partials.place-card', ['place' => $place])->render())
                ->implode("\n");

            return $this->cardsJson($html, $places->nextPageUrl(), $places->lastItem(), $places->total());
        }

        // Past the last page (old link, results shrank): go to the last page that has results.
        if ($places->currentPage() > $places->lastPage()) {
            $last = $places->lastPage();
            return redirect()->to($last > 1 ? $places->url($last) : $request->fullUrlWithoutQuery('page'));
        }

        $response = response()->view('places', [
            'lang'      => $lang,
            'activeNav' => 'places',
            'places'    => $places,
            'q'         => $q,
            'sort'      => $sort,
            'sorts'     => self::SORTS,
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
