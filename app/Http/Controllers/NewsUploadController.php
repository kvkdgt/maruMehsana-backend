<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Api\ExternalNewsController;
use App\Models\NewsArticle;
use Illuminate\Http\Request;

/**
 * Simple password-protected page for publishing news from a phone.
 *
 * GET  /news-upload          password screen, or the upload form once unlocked
 * POST /news-upload/unlock   check the password and unlock for this session
 * POST /news-upload          publish an article (same logic as POST /api/external/news)
 * POST /news-upload/lock     lock the page again
 *
 * Publishing goes through ExternalNewsController, so validation, agency and
 * image handling stay identical to the API. The API key never reaches the browser.
 */
class NewsUploadController extends Controller
{
    private const SESSION_KEY = 'news_upload_unlocked';

    public function __construct()
    {
        // Keep this page out of search engines, previews and caches. Sent on every
        // response (including redirects), on top of the <meta name="robots"> tag.
        // Deliberately NOT listed in robots.txt: that file is public and would reveal the URL.
        $this->middleware(function ($request, $next) {
            $response = $next($request);
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet, noimageindex');
            $response->headers->set('Referrer-Policy', 'no-referrer');
            $response->headers->set('Cache-Control', 'no-store, private');
            return $response;
        });
    }

    public function show(Request $request)
    {
        return view('news-upload', [
            'unlocked' => $request->session()->get(self::SESSION_KEY, false),
        ]);
    }

    public function unlock(Request $request)
    {
        $hash = strtolower((string) config('services.news_upload.password_sha256'));
        $given = (string) $request->input('password', '');

        if ($hash === '' || $given === '' || !hash_equals($hash, hash('sha256', $given))) {
            return back()->withErrors(['password' => 'Wrong password.']);
        }

        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, true);

        return redirect()->route('news-upload');
    }

    public function lock(Request $request)
    {
        $request->session()->forget(self::SESSION_KEY);
        $request->session()->regenerateToken();

        return redirect()->route('news-upload');
    }

    public function store(Request $request, ExternalNewsController $api)
    {
        if (!$request->session()->get(self::SESSION_KEY, false)) {
            return redirect()->route('news-upload');
        }

        $response = $api->store($request);
        $body = $response->getData(true);

        if ($response->getStatusCode() === 201) {
            $message = 'News published: ' . $body['data']['title'];

            // Same as the agency panel: schedule a push to all users (sent 5 minutes later).
            if ($request->boolean('send_notification')) {
                $article = NewsArticle::find($body['data']['id']);
                $result = $article && $article->is_active
                    ? app(NotificationController::class)->scheduleNewsNotification($article)
                    : ['success' => false, 'message' => 'article is not active'];

                $message .= $result['success']
                    ? '. Notification will be sent at ' . $result['scheduled_at']->format('h:i A') . '.'
                    : '. Notification NOT scheduled: ' . $result['message'];
            }

            return redirect()->route('news-upload')->with('success', $message);
        }

        $errors = $body['errors'] ?? ['general' => [$body['message'] ?? 'Could not publish the news.']];

        return back()->withInput()->withErrors($errors);
    }
}
