<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\JobVacancy;
use App\Models\NewsArticle;
use App\Models\TouristPlace;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use XMLWriter;

/**
 * GET /sitemap.xml    -> XML sitemap of the public website (Gujarati URL + ?lang=en alternate).
 * GET /news/feed.xml  -> RSS 2.0 feed of the latest news.
 *
 * Both are cached (sitemap 1 hour, feed 10 minutes). The cache key includes the host,
 * so absolute URLs always match the domain the request came in on.
 * One sitemap file is enough: the site has far fewer than the 50,000 URLs a file may hold.
 */
class SitemapController extends Controller
{
    private const SITEMAP_TTL = 3600;
    private const FEED_TTL = 600;
    private const FEED_ITEMS = 30;

    public function index(Request $request)
    {
        $xml = Cache::remember(
            'site:sitemap:' . $request->getSchemeAndHttpHost(),
            self::SITEMAP_TTL,
            fn () => $this->buildSitemap()
        );

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function newsFeed(Request $request)
    {
        $xml = Cache::remember(
            'site:news-feed:' . $request->getSchemeAndHttpHost(),
            self::FEED_TTL,
            fn () => $this->buildNewsFeed()
        );

        return response($xml, 200)->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    /** News shown on the website: active articles of an active, non-deleted agency. */
    private function visibleNews(): Builder
    {
        // news_agencies.status is a boolean column; whereHas() skips soft-deleted agencies.
        return NewsArticle::query()
            ->where('is_active', true)
            ->whereHas('agency', fn ($q) => $q->where('status', true));
    }

    private function buildSitemap(): string
    {
        $businesses = Business::query()->orderBy('id')->get(['id', 'updated_at']);
        $places = TouristPlace::query()->orderBy('id')->get(['id', 'updated_at']);
        $news = $this->visibleNews()->orderByDesc('id')->get(['id', 'slug', 'updated_at']);
        // Same visibility as /jobs: active, open or filled, not expired.
        $jobs = JobVacancy::active()->orderByDesc('id')->get(['id', 'updated_at']);

        $latest = fn ($items) => $items->pluck('updated_at')->filter()->max();

        $w = $this->startDocument();
        $w->startElement('urlset');
        $w->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $w->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        $home = url('/');
        $this->writeUrl($w, $home, collect([$latest($businesses), $latest($places), $latest($news), $latest($jobs)])->filter()->max());
        $this->writeUrl($w, route('businesses'), $latest($businesses));
        $this->writeUrl($w, route('places'), $latest($places));
        $this->writeUrl($w, route('news'), $latest($news));
        $this->writeUrl($w, route('jobs'), $latest($jobs));

        foreach ($businesses as $b) {
            $this->writeUrl($w, route('businesses.show', $b->id), $b->updated_at);
        }
        foreach ($places as $p) {
            $this->writeUrl($w, route('places.show', $p->id), $p->updated_at);
        }
        foreach ($news as $n) {
            $this->writeUrl($w, $n->web_url, $n->updated_at);
        }
        foreach ($jobs as $j) {
            $this->writeUrl($w, route('jobs.show', $j->id), $j->updated_at);
        }

        // Static page, English only (no language switch), so no alternates.
        $privacyView = resource_path('views/privacy.blade.php');
        $privacyMtime = is_file($privacyView) ? \Carbon\Carbon::createFromTimestamp(filemtime($privacyView)) : null;
        $this->writeUrl($w, url('/privacy-policy'), $privacyMtime, false);

        $w->endElement();
        $w->endDocument();

        return $w->outputMemory();
    }

    private function writeUrl(XMLWriter $w, string $loc, ?CarbonInterface $lastmod, bool $bilingual = true): void
    {
        $w->startElement('url');
        $w->writeElement('loc', $loc);
        if ($lastmod) {
            $w->writeElement('lastmod', $lastmod->copy()->utc()->toW3cString());
        }
        if ($bilingual) {
            foreach (['gu' => $loc, 'en' => $this->englishUrl($loc), 'x-default' => $loc] as $hreflang => $href) {
                $w->startElement('xhtml:link');
                $w->writeAttribute('rel', 'alternate');
                $w->writeAttribute('hreflang', $hreflang);
                $w->writeAttribute('href', $href);
                $w->endElement();
            }
        }
        $w->endElement();
    }

    private function englishUrl(string $url): string
    {
        return $url . (str_contains($url, '?') ? '&' : '?') . 'lang=en';
    }

    private function buildNewsFeed(): string
    {
        $articles = $this->visibleNews()
            ->latest()
            ->orderByDesc('id')
            ->limit(self::FEED_ITEMS)
            ->get(['id', 'title', 'slug', 'excerpt', 'image', 'created_at', 'updated_at']);

        $w = $this->startDocument();
        $w->startElement('rss');
        $w->writeAttribute('version', '2.0');
        $w->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
        $w->writeAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');

        $w->startElement('channel');
        $w->writeElement('title', 'મારું મહેસાણા સમાચાર | Maru Mehsana News');
        $w->writeElement('link', route('news'));
        $w->writeElement('description', 'મહેસાણાના તાજા સમાચાર – Latest local news from Mehsana.');
        $w->writeElement('language', 'gu');

        $w->startElement('atom:link');
        $w->writeAttribute('href', route('news.feed'));
        $w->writeAttribute('rel', 'self');
        $w->writeAttribute('type', 'application/rss+xml');
        $w->endElement();

        $newest = $articles->max(fn ($a) => $a->updated_at ?? $a->created_at);
        if ($newest) {
            $w->writeElement('lastBuildDate', $newest->toRfc2822String());
        }

        foreach ($articles as $a) {
            $link = $a->web_url;

            $w->startElement('item');
            $w->writeElement('title', (string) $a->title);
            $w->writeElement('link', $link);

            $w->startElement('guid');
            $w->writeAttribute('isPermaLink', 'true');
            $w->text($link);
            $w->endElement();

            if ($a->created_at) {
                $w->writeElement('pubDate', $a->created_at->toRfc2822String());
            }

            $excerpt = trim((string) $a->getRawOriginal('excerpt'));
            if ($excerpt !== '') {
                // Plain text, escaped by XMLWriter (safe even if the text contains "]]>").
                $w->writeElement('description', Str::of(strip_tags($excerpt))->squish()->toString());
            }

            if ($a->image) {
                $w->startElement('media:content');
                $w->writeAttribute('url', $a->image_url);
                $w->writeAttribute('medium', 'image');
                $w->endElement();
            }

            $w->endElement();
        }

        $w->endElement(); // channel
        $w->endElement(); // rss
        $w->endDocument();

        return $w->outputMemory();
    }

    private function startDocument(): XMLWriter
    {
        $w = new XMLWriter();
        $w->openMemory();
        $w->setIndent(true);
        $w->setIndentString('  ');
        $w->startDocument('1.0', 'UTF-8');

        return $w;
    }
}
