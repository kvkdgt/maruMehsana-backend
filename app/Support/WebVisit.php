<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Website view counting for the `web_visitors` columns.
 *
 * A view is not counted when the page is served. The page gets a signed one-time token,
 * and a small script in the browser sends it back to POST /visit after the page has been
 * visible for a few seconds. Bots that do not run JavaScript, bots with a bot user agent,
 * automated browsers (navigator.webdriver), replayed or forged tokens, and pages closed
 * at once are therefore not counted.
 */
class WebVisit
{
    /** Type sent by the page => table that holds its `web_visitors` column. */
    public const TABLES = [
        'business' => 'businesses',
        'category' => 'categories',
        'place'    => 'tourist_places',
        'news'     => 'news_articles',
        'job'      => 'job_vacancies',
    ];

    /** The browser must keep the page visible this long before it reports the view. */
    public const MIN_SECONDS = 3;

    /** A token older than this is refused (page left open, then sent much later). */
    public const MAX_AGE_SECONDS = 1800;

    private const BOT_UA = '/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|preview|fetch|monitor|curl|wget|python|java\/|httpclient|okhttp|axios|node-fetch|go-http|headless|phantom|selenium|puppeteer|playwright|lighthouse|pagespeed|gtmetrix|pingdom|uptime/i';

    /** Signed token for one view of one item: "<issued unix time>.<hmac>". */
    public static function token(string $type, int $id): string
    {
        $ts = time();

        return $ts . '.' . self::sign($type, $id, $ts);
    }

    /** True when the user agent is empty or looks like a bot, tool or link preview. */
    public static function isBotUserAgent(?string $ua): bool
    {
        $ua = trim((string) $ua);

        return $ua === '' || preg_match(self::BOT_UA, $ua) === 1;
    }

    /**
     * Count the view if the request is a valid report from a real browser.
     * Returns true when counted.
     */
    public static function record(Request $request): bool
    {
        $type  = (string) $request->input('type');
        $id    = (int) $request->input('id');
        $token = (string) $request->input('token');

        if (!isset(self::TABLES[$type]) || $id <= 0 || self::isBotUserAgent($request->userAgent())) {
            return false;
        }

        [$ts, $sig] = array_pad(explode('.', $token, 2), 2, '');
        $ts  = (int) $ts;
        $age = time() - $ts;
        if ($ts <= 0 || !hash_equals(self::sign($type, $id, $ts), $sig)
            || $age < self::MIN_SECONDS - 1 || $age > self::MAX_AGE_SECONDS) {
            return false;
        }

        // One count per token: a refresh gets a new token, a replayed request does not.
        if (!Cache::add('web_visit:' . $sig, 1, self::MAX_AGE_SECONDS + 60)) {
            return false;
        }

        try {
            // Query builder, not Eloquent: a view must not bump updated_at.
            DB::table(self::TABLES[$type])->where('id', $id)->increment('web_visitors');
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return true;
    }

    private static function sign(string $type, int $id, int $ts): string
    {
        return hash_hmac('sha256', "web_visit|{$type}|{$id}|{$ts}", (string) config('app.key'));
    }
}
