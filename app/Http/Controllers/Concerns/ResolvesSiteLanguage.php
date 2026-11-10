<?php

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Language for the public website pages (Gujarati default, English optional).
 * ?lang=gu|en wins and is remembered in a cookie, then the cookie, then Gujarati.
 */
trait ResolvesSiteLanguage
{
    protected static array $siteLocales = ['gu', 'en'];
    protected static string $siteLangCookie = 'mm_lang';

    protected function resolveSiteLanguage(Request $request): string
    {
        $fromQuery = $request->query('lang');
        $lang = in_array($fromQuery, self::$siteLocales, true)
            ? $fromQuery
            : $request->cookie(self::$siteLangCookie);

        if (!in_array($lang, self::$siteLocales, true)) {
            $lang = 'gu';
        }

        app()->setLocale($lang);
        Carbon::setLocale($lang);

        return $lang;
    }

    /** Remember an explicit ?lang= choice for later visits. */
    protected function rememberSiteLanguage($response, Request $request, string $lang)
    {
        if (in_array($request->query('lang'), self::$siteLocales, true)) {
            $response->cookie(self::$siteLangCookie, $lang, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'Lax');
        }

        return $response;
    }
}
