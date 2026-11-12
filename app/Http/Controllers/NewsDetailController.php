<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesSiteLanguage;
use App\Models\NewsArticle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Public web detail page for a news article: GET /news/{id}/{slug}
 * (the app share link /news/{id} is separate and handled by ShareController).
 */
class NewsDetailController extends Controller
{
    use ResolvesSiteLanguage;

    private const RELATED = 6;
    private const MOST_READ = 5;

    public function show(Request $request, int $id, string $slug)
    {
        $article = $this->visible(NewsArticle::query())
            ->with('agency:id,name,logo,status')
            ->findOrFail($id);

        // Old or mistyped slugs go to the one canonical URL (query string such as ?lang=en kept).
        $canonicalSlug = $article->slug ?: 'news-' . $article->id;
        if ($slug !== $canonicalSlug) {
            $qs = $request->getQueryString();

            return redirect()->to($article->web_url . ($qs ? '?' . $qs : ''), 301);
        }

        $lang = $this->resolveSiteLanguage($request);

        $this->countWebVisit($request, $article->id);

        $columns = ['id', 'title', 'slug', 'image', 'is_for_mehsana', 'visitor', 'web_visitors', 'agency_id', 'created_at'];

        // Latest other articles, same "for Mehsana" kind first.
        $related = $this->visible(NewsArticle::query())
            ->with('agency:id,name')
            ->whereKeyNot($article->id)
            ->orderByRaw('CASE WHEN is_for_mehsana = ? THEN 0 ELSE 1 END', [$article->is_for_mehsana ? 1 : 0])
            ->latest()
            ->orderByDesc('id')
            ->limit(self::RELATED)
            ->get($columns);

        $mostRead = $this->visible(NewsArticle::query())
            ->whereKeyNot($article->id)
            ->whereNotIn('id', $related->pluck('id'))
            ->whereRaw('(visitor + web_visitors) > 0')
            ->orderByRaw('(visitor + web_visitors) desc')
            ->orderByDesc('id')
            ->limit(self::MOST_READ)
            ->get($columns);

        $response = response()->view('news-detail', [
            'lang'      => $lang,
            'activeNav' => 'news',
            'article'   => $article,
            'related'   => $related,
            'mostRead'  => $mostRead,
        ]);

        return $this->rememberSiteLanguage($response, $request, $lang);
    }

    /**
     * Articles the public may see: active and from an enabled, not deleted agency
     * (same rule as the sitemap and RSS feed; whereHas skips soft-deleted agencies).
     * NewsAgency::scopeActive() is not used: it compares the boolean status to 'active'.
     */
    private function visible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereHas('agency', fn (Builder $a) => $a->where('status', true));
    }

    /**
     * Count one website view, kept apart from the app's `visitor` counter like businesses do.
     * Only runs when news_articles has a `web_visitors` column. Skips HEAD requests and bots.
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
            $hasColumn ??= Schema::hasColumn('news_articles', 'web_visitors');
            if ($hasColumn) {
                // Query builder, not Eloquent: a view must not bump updated_at (used as dateModified).
                DB::table('news_articles')->where('id', $id)->increment('web_visitors');
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
