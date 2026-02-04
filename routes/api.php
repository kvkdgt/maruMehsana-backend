<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\NewsArticle;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessEnquiryController;
use App\Http\Controllers\FactsController;
use App\Http\Controllers\TouristPlaceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BannerAdController;
use App\Http\Controllers\FcmController;
use App\Http\Controllers\BusinessReviewController;
use App\Http\Controllers\TouristPlaceReviewController;
use App\Http\Controllers\ShareImageController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/admin/signup', [AdminController::class, 'signup']);
Route::get('/search', [AdminController::class, 'search']);


Route::get('/trending-categories', [CategoryController::class, 'trendingCategories']);
Route::get('/categories', [CategoryController::class, 'categories']);
Route::get('/businesses/get', [BusinessController::class, 'getBusinesses']);
Route::get('/getBusinessById', [BusinessController::class, 'getBusinessByIdAPI']);

Route::post('/user/store', [UserController::class, 'store']);
Route::post('/user/signup', [UserController::class, 'signup']);
Route::post('/user/login', [UserController::class, 'login']);
Route::post('/user/upgrade-guest', [UserController::class, 'upgradeGuest']);
Route::get('/user/notifications', [UserController::class, 'getNotifications']);
Route::post('/user/notifications/mark-read', [UserController::class, 'markNotificationAsRead']);
Route::post('/user/notifications/delete', [UserController::class, 'deleteNotification']);
Route::post('/user/notifications/clear-read', [UserController::class, 'clearReadNotifications']);
Route::get('/user/notifications/unread-count', [UserController::class, 'getUnreadNotificationCount']);
Route::get('/facts/get', [FactsController::class, 'getFacts']);

Route::get('/tourist-places', [TouristPlaceController::class, 'index']);
Route::get('/tourist-places/{id}', [TouristPlaceController::class, 'show']);

Route::post('/enquiry/submit', [BusinessEnquiryController::class, 'create']);
Route::get('/banner-ads', [BannerAdController::class, 'getActiveBanners']);
Route::post('/banner-ads/{id}/increment-touch', [BannerAdController::class, 'incrementTouch']);
Route::post('send-fcm-notification', [FcmController::class, 'sendFcmNotification']);

// Business Reviews
Route::get('/businesses/{id}/reviews', [BusinessReviewController::class, 'getBusinessReviews']);
Route::post('/reviews', [BusinessReviewController::class, 'store']);
Route::put('/reviews/{id}', [BusinessReviewController::class, 'update']);
Route::delete('/reviews/{id}', [BusinessReviewController::class, 'destroy']);

// Tourist Place Reviews
Route::get('/tourist-places/{id}/reviews', [TouristPlaceReviewController::class, 'getPlaceReviews']);
Route::post('/place-reviews', [TouristPlaceReviewController::class, 'store']);
Route::put('/place-reviews/{id}', [TouristPlaceReviewController::class, 'update']);
Route::delete('/place-reviews/{id}', [TouristPlaceReviewController::class, 'destroy']);


// Share Images
Route::get('/share/place/{id}', [ShareImageController::class, 'sharePlace']);
Route::get('/share/news/{id}', [ShareImageController::class, 'shareNews']);

// News API Routes for Mobile App
Route::prefix('news')->group(function () {

    // Helper function to clean malformed UTF-8 strings
    if (!function_exists('cleanUtf8')) {
        function cleanUtf8($string) {
            return is_string($string) ? mb_convert_encoding($string, 'UTF-8', 'UTF-8') : $string;
        }
    }

    // Helper function to transform article data
    function transformArticle($article, $includeContent = true) {
        $data = [
            'id' => $article->id,
            'title' => cleanUtf8($article->title),
            'slug' => cleanUtf8($article->slug),
            'excerpt' => cleanUtf8($article->excerpt),
            'image' => cleanUtf8($article->image),
            'image_url' => cleanUtf8($article->image_url),
            'is_active' => $article->is_active,
            'is_featured' => $article->is_featured,
            'is_for_mehsana' => $article->is_for_mehsana,
            'visitor' => $article->visitor,
            'created_at' => $article->created_at,
            'updated_at' => $article->updated_at,
            'agency' => [
                'id' => optional($article->agency)->id,
                'name' => cleanUtf8(optional($article->agency)->name),
                'logo' => cleanUtf8(optional($article->agency)->logo),
                'logo_url' => cleanUtf8(optional($article->agency)->logo_url),
                'initial' => cleanUtf8(optional($article->agency)->initial),
                'is_active' => optional($article->agency)->is_active
            ]
        ];

        if ($includeContent) {
            $data['content'] = cleanUtf8($article->content);
        }

        return $data;
    }

    // Get all active news articles with pagination and agency details
    Route::get('/', function (Request $request) {
        try {
            $query = NewsArticle::with(['agency' => function ($q) {
                $q->select('id', 'name', 'logo', 'status');
            }])->active();

            switch ($request->get('sort', 'latest')) {
                case 'popular':
                    $query->popular();
                    break;
                case 'featured':
                    $query->featured()->latest();
                    break;
                default:
                    $query->latest();
                    break;
            }

            $articles = $query->paginate($request->get('per_page', 10));

            $transformedArticles = $articles->getCollection()->map(function ($article) {
                return transformArticle($article);
            });

            return response()->json([
                'status' => 'success',
                'data' => $transformedArticles,
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                    'has_more' => $articles->hasMorePages()
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching articles: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    });

    // Get featured articles with agency details
    Route::get('/featured', function () {
        try {
            $articles = NewsArticle::with(['agency' => function ($q) {
                $q->select('id', 'name', 'logo', 'status');
            }])->active()
                ->featured()
                ->latest()
                ->limit(5)
                ->get();

            $transformedArticles = $articles->map(function ($article) {
                return transformArticle($article);
            });

            return response()->json([
                'status' => 'success',
                'data' => $transformedArticles
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching featured articles: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    });

    // Get popular articles with agency details
    Route::get('/popular', function (Request $request) {
        try {
            $articles = NewsArticle::with(['agency' => function ($q) {
                $q->select('id', 'name', 'logo', 'status');
            }])->active()
                ->popular()
                ->limit($request->get('limit', 10))
                ->get();

            $transformedArticles = $articles->map(function ($article) {
                return transformArticle($article, false); // Don't include content for popular list
            });

            return response()->json([
                'status' => 'success',
                'data' => $transformedArticles
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching popular articles: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    });

    // Get all agencies (for mobile app reference)
    Route::get('/agencies', function () {
        try {
            $agencies = \App\Models\NewsAgency::active()
                ->select('id', 'name', 'logo', 'status')
                ->get()
                ->map(function ($agency) {
                    return [
                        'id' => $agency->id,
                        'name' => cleanUtf8($agency->name),
                        'logo' => cleanUtf8($agency->logo),
                        'logo_url' => cleanUtf8($agency->logo_url),
                        'initial' => cleanUtf8($agency->initial),
                        'is_active' => $agency->is_active
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $agencies
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching agencies: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    });

    // Search articles with agency details
    Route::get('/search/{query}', function ($query, Request $request) {
        try {
            $articlesQuery = NewsArticle::with(['agency' => function ($q) {
                $q->select('id', 'name', 'logo', 'status');
            }])->active()->search($query);

            switch ($request->get('sort', 'latest')) {
                case 'popular':
                    $articlesQuery->popular();
                    break;
                default:
                    $articlesQuery->latest();
                    break;
            }

            $articles = $articlesQuery->paginate($request->get('per_page', 10));

            $transformedArticles = $articles->getCollection()->map(function ($article) {
                return transformArticle($article, false); // Don't include content for search results
            });

            return response()->json([
                'status' => 'success',
                'data' => $transformedArticles,
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                    'has_more' => $articles->hasMorePages()
                ],
                'query' => $query
            ]);
        } catch (\Throwable $e) {
            Log::error('Error searching articles: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    });

    // Get articles by category (Mehsana or General) with agency details
    Route::get('/category/{type}', function ($type, Request $request) {
        try {
            $isMehsana = strtolower($type) === 'mehsana';

            $query = NewsArticle::with(['agency' => function ($q) {
                $q->select('id', 'name', 'logo', 'status');
            }])->active()->where('is_for_mehsana', $isMehsana);

            switch ($request->get('sort', 'latest')) {
                case 'popular':
                    $query->popular();
                    break;
                case 'featured':
                    $query->featured()->latest();
                    break;
                default:
                    $query->latest();
                    break;
            }

            $articles = $query->paginate($request->get('per_page', 10));

            $transformedArticles = $articles->getCollection()->map(function ($article) {
                return transformArticle($article);
            });

            return response()->json([
                'status' => 'success',
                'data' => $transformedArticles,
                'category' => $type,
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                    'has_more' => $articles->hasMorePages()
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching articles by category: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    });

    // Get articles by specific agency
    Route::get('/agency/{agencyId}', function ($agencyId, Request $request) {
        try {
            $query = NewsArticle::with(['agency' => function ($q) {
                $q->select('id', 'name', 'logo', 'status');
            }])->active()->where('agency_id', $agencyId);

            switch ($request->get('sort', 'latest')) {
                case 'popular':
                    $query->popular();
                    break;
                case 'featured':
                    $query->featured()->latest();
                    break;
                default:
                    $query->latest();
                    break;
            }

            $articles = $query->paginate($request->get('per_page', 10));

            if ($articles->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No articles found for this agency'
                ], 404);
            }

            $transformedArticles = $articles->getCollection()->map(function ($article) {
                return transformArticle($article, false); // Don't include content for agency list
            });

            return response()->json([
                'status' => 'success',
                'data' => $transformedArticles,
                'agency_id' => $agencyId,
                'pagination' => [
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'per_page' => $articles->perPage(),
                    'total' => $articles->total(),
                    'has_more' => $articles->hasMorePages()
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching articles by agency: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    });

    // Get specific article by ID and increment visitor count
    Route::get('/{id}', function ($id) {
        try {
            $article = NewsArticle::with(['agency' => function ($q) {
                $q->select('id', 'name', 'logo', 'status');
            }])->active()->find($id);

            if (!$article) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Article not found'
                ], 404);
            }

            // Increment visitor count
            $article->incrementVisitor();

            $transformedArticle = transformArticle($article);
            $transformedArticle['visitor'] = $article->visitor + 1; // Include incremented count

            return response()->json([
                'status' => 'success',
                'data' => $transformedArticle
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching article by ID: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    });

    // Get article by slug and increment visitor count
    Route::get('/slug/{slug}', function ($slug) {
        try {
            $article = NewsArticle::with(['agency' => function ($q) {
                $q->select('id', 'name', 'logo', 'status');
            }])->active()->where('slug', $slug)->first();

            if (!$article) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Article not found'
                ], 404);
            }

            // Increment visitor count
            $article->incrementVisitor();

            $transformedArticle = transformArticle($article);
            $transformedArticle['visitor'] = $article->visitor + 1; // Include incremented count

            return response()->json([
                'status' => 'success',
                'data' => $transformedArticle
            ]);
        } catch (\Throwable $e) {
            Log::error('Error fetching article by slug: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    });

});