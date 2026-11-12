<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesSiteLanguage;
use App\Models\NewsArticle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Public "News" page: search, filter chips, sort, paginate (with infinite scroll).
 * GET /news?q=&filter=mehsana|featured&sort=newest|popular&page=
 * The article page (/news/{id}/{slug}) lives in NewsDetailController.
 */
class NewsListController extends Controller
{
    use ResolvesSiteLanguage;

    private const FILTERS = ['all', 'mehsana', 'featured'];
    private const SORTS = ['newest', 'popular'];
    private const PER_PAGE = 12;

    public function index(Request $request)
    {
        $lang = $this->resolveSiteLanguage($request);

        // Same page rules as /places: ?page must be a whole number >= 2, otherwise the clean URL.
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
        $filter = in_array($request->query('filter'), self::FILTERS, true) ? $request->query('filter') : 'all';
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'newest';

        // Base: active articles from agencies that are active and not deleted (whereHas skips soft-deleted
        // agencies). Same rule as the sitemap and RSS feed. NewsAgency::scopeActive() is not used: it compares
        // the boolean status column with 'active'.
        $base = NewsArticle::query()
            ->where('is_active', true)
            ->whereHas('agency', fn (Builder $a) => $a->where('status', true));

        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $base->where(function (Builder $w) use ($like) {
                $w->where('title', 'like', $like)->orWhere('excerpt', 'like', $like);
            });
        }

        // Chip counts follow the search text, so each chip says how many results it would show.
        $counts = [
            'all'      => (clone $base)->count(),
            'mehsana'  => (clone $base)->where('is_for_mehsana', true)->count(),
            'featured' => (clone $base)->where('is_featured', true)->count(),
        ];

        $query = (clone $base)
            ->select(['id', 'title', 'slug', 'excerpt', 'image', 'is_featured', 'is_for_mehsana', 'visitor', 'web_visitors', 'agency_id', 'created_at'])
            ->with('agency:id,name');

        match ($filter) {
            'mehsana'  => $query->where('is_for_mehsana', true),
            'featured' => $query->where('is_featured', true),
            default    => null,
        };

        // Lead story: the newest featured article, shown big on page 1 of the plain list. It is left out of the
        // grid on every page of that view (not just page 1) so infinite scroll pages line up without duplicates.
        $lead = null;
        if ($q === '' && $filter === 'all' && $sort === 'newest') {
            $lead = (clone $query)->where('is_featured', true)->orderByDesc('created_at')->orderByDesc('id')->first();
            if ($lead) {
                $query->whereKeyNot($lead->id);
            }
        }
        $leadCount = $lead ? 1 : 0;

        match ($sort) {
            'popular' => $query->orderByRaw('(visitor + web_visitors) desc')->orderByDesc('created_at'),
            default   => $query->orderByDesc('created_at'),
        };
        $query->orderByDesc('id');

        $articles = $query->paginate(self::PER_PAGE, ['*'], 'page', $page)->withQueryString();

        if ($wantsCards) {
            if ($articles->currentPage() > $articles->lastPage()) {
                return $this->cardsJson('', null);
            }
            $html = $articles->getCollection()
                ->map(fn ($article) => view('partials.news-card', ['article' => $article, 'lang' => $lang])->render())
                ->implode("\n");

            return $this->cardsJson($html, $articles->nextPageUrl(), $articles->lastItem() + $leadCount, $articles->total() + $leadCount);
        }

        // Past the last page (old link, results shrank): go to the last page that has results.
        if ($articles->currentPage() > $articles->lastPage()) {
            $last = $articles->lastPage();
            return redirect()->to($last > 1 ? $articles->url($last) : $request->fullUrlWithoutQuery('page'));
        }

        $response = response()->view('news-list', [
            'lang'      => $lang,
            'activeNav' => 'news',
            'articles'  => $articles,
            'lead'      => $articles->onFirstPage() ? $lead : null,
            'total'     => $articles->total() + $leadCount,
            'counts'    => $counts,
            'q'         => $q,
            'filter'    => $filter,
            'filters'   => self::FILTERS,
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
