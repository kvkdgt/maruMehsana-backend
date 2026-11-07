<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Guards the external "add news" API with a shared secret sent in the
 * X-API-Key header.
 *
 * The key is accepted if it matches either:
 *   - services.news_api.key         (plain key from .env, optional), or
 *   - services.news_api.key_sha256  (SHA-256 hash stored in config, so the
 *                                    real key never lives in the repository).
 *
 * Fails closed: if neither is configured, every request is rejected.
 */
class VerifyNewsApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $plain = (string) config('services.news_api.key');
        $hash  = strtolower((string) config('services.news_api.key_sha256'));

        if ($plain === '' && $hash === '') {
            return response()->json([
                'status'  => 'error',
                'message' => 'News API is not configured on the server.',
            ], 503);
        }

        $provided = (string) $request->header('X-API-Key', '');

        $valid = $provided !== '' && (
            ($plain !== '' && hash_equals($plain, $provided)) ||
            ($hash !== '' && hash_equals($hash, hash('sha256', $provided)))
        );

        if (!$valid) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid or missing API key.',
            ], 401, ['WWW-Authenticate' => 'ApiKey header="X-API-Key"']);
        }

        return $next($request);
    }
}
