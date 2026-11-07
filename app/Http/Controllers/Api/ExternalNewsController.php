<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsAgency;
use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * POST /api/external/news
 *
 * Lets an external system publish a news article. Protected by the
 * "news.api.key" middleware. Every article is attached to the single news
 * agency whose username is configured in services.news_api.agency_username
 * (default "MaruMehsana"), never to an agency supplied by the caller.
 *
 * NOTE: unlike the agency panel, this endpoint intentionally does NOT schedule
 * a push notification for the new article.
 */
class ExternalNewsController extends Controller
{
    public function store(Request $request)
    {
        $agency = NewsAgency::where('username', config('services.news_api.agency_username'))->first();

        if (!$agency) {
            return response()->json([
                'status'  => 'error',
                'message' => 'The publishing news agency was not found on the server.',
            ], 500);
        }

        if (!$agency->status) {
            return response()->json([
                'status'  => 'error',
                'message' => 'The publishing news agency is not active.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'          => 'required|string|max:255|unique:news_articles,title',
            'excerpt'        => 'required|string|max:500',
            'content'        => 'required|string',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active'      => 'nullable|boolean',
            'is_featured'    => 'nullable|boolean',
            'is_for_mehsana' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        unset($data['image']);

        $data['agency_id']      = $agency->id;
        $data['is_active']      = $request->boolean('is_active', true);
        $data['is_featured']    = $request->boolean('is_featured', false);
        $data['is_for_mehsana'] = $request->boolean('is_for_mehsana', false);

        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $imageName = time() . '_' . Str::random(8) . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/news', $imageName);
            $data['image'] = $imageName;
        }

        $article = NewsArticle::create($data);

        // Gujarati titles slugify to an empty string; keep slugs usable.
        if ($article->slug === '' || $article->slug === null) {
            $article->slug = 'news-' . $article->id;
            $article->save();
        }

        // Intentionally NO push notification here (unlike the agency panel).
        return response()->json([
            'status'  => 'success',
            'message' => 'News article created.',
            'data'    => [
                'id'             => $article->id,
                'title'          => $article->title,
                'slug'           => $article->slug,
                'image_url'      => $article->image_url,
                'is_active'      => $article->is_active,
                'is_featured'    => $article->is_featured,
                'is_for_mehsana' => $article->is_for_mehsana,
                'agency'         => ['id' => $agency->id, 'name' => $agency->name, 'username' => $agency->username],
                'notification_sent' => false,
                'created_at'     => $article->created_at,
            ],
        ], 201);
    }
}
