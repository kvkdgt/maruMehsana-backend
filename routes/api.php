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
Route::get('/facts/get', [FactsController::class, 'getFacts']);

Route::get('/tourist-places', [TouristPlaceController::class, 'index']);
Route::get('/tourist-places/{id}', [TouristPlaceController::class, 'show']);

Route::post('/enquiry/submit', [BusinessEnquiryController::class, 'create']);
Route::get('/banner-ads', [BannerAdController::class, 'getActiveBanners']);
Route::post('/banner-ads/{id}/increment-touch', [BannerAdController::class, 'incrementTouch']);
Route::post('send-fcm-notification', [FcmController::class, 'sendFcmNotification']);



// News API Routes for Mobile App
Route::prefix('news')->group(function () {
    
    // Get all active news articles with pagination and agency details
    // Route::get('/', function (Request $request) {
    //     $query = NewsArticle::with(['agency' => function($q) {
    //         $q->select('id', 'name', 'logo', 'status');
    //     }])->active();
    //     // Add sorting options
    //     switch ($request->get('sort', 'latest')) {
    //         case 'popular':
    //             $query->popular();
    //             break;
    //         case 'featured':
    //             $query->featured()->latest();
    //             break;
    //         default:
    //             $query->latest();
    //             break;
    //     }

    //     $articles = $query->paginate($request->get('per_page', 10));

    //     // Transform the data to include agency details
    //     $transformedArticles = $articles->getCollection()->map(function ($article) {
    //         return [
    //             'id' => $article->id,
    //             'title' => $article->title,
    //             'slug' => $article->slug,
    //             'excerpt' => $article->excerpt,
    //             'content' => $article->content,
    //             'image' => $article->image,
    //             'image_url' => $article->image_url,
    //             'is_active' => $article->is_active,
    //             'is_featured' => $article->is_featured,
    //             'is_for_mehsana' => $article->is_for_mehsana,
    //             'visitor' => $article->visitor,
    //             'created_at' => $article->created_at,
    //             'updated_at' => $article->updated_at,
    //             'agency' => [
    //                 'id' => $article->agency->id,
    //                 'name' => $article->agency->name,
    //                 'logo' => $article->agency->logo,
    //                 'logo_url' => $article->agency->logo_url,
    //                 'initial' => $article->agency->initial,
    //                 'is_active' => $article->agency->is_active
    //             ]
    //         ];
    //     });

    //     return response()->json([
    //         'status' => 'success',
    //         'data' => $transformedArticles,
    //         'pagination' => [
    //             'current_page' => $articles->currentPage(),
    //             'last_page' => $articles->lastPage(),
    //             'per_page' => $articles->perPage(),
    //             'total' => $articles->total(),
    //             'has_more' => $articles->hasMorePages()
    //         ]
    //     ]);
    // });

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

        // Sanitize output
        $transformedArticles = $articles->getCollection()->map(function ($article) {
            return [
                'id' => $article->id,
                'title' => cleanUtf8($article->title),
                'slug' => cleanUtf8($article->slug),
                'excerpt' => cleanUtf8($article->excerpt),
                'content' => cleanUtf8($article->content),
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
            'error' => $e->getMessage(),
        ], 500);
    }
});

// Helper to clean malformed UTF-8 strings
function cleanUtf8($string)
{
    return is_string($string) ? mb_convert_encoding($string, 'UTF-8', 'UTF-8') : $string;
}


    // Get featured articles with agency details
    Route::get('/featured', function () {
        $articles = NewsArticle::with(['agency' => function($q) {
            $q->select('id', 'name', 'logo', 'status');
        }])->active()
            ->featured()
            ->latest()
            ->limit(5)
            ->get();

        $transformedArticles = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'image' => $article->image,
                'image_url' => $article->image_url,
                'is_active' => $article->is_active,
                'is_featured' => $article->is_featured,
                'is_for_mehsana' => $article->is_for_mehsana,
                'visitor' => $article->visitor,
                'created_at' => $article->created_at,
                'updated_at' => $article->updated_at,
                'agency' => [
                    'id' => $article->agency->id,
                    'name' => $article->agency->name,
                    'logo' => $article->agency->logo,
                    'logo_url' => $article->agency->logo_url,
                    'initial' => $article->agency->initial,
                    'is_active' => $article->agency->is_active
                ]
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $transformedArticles
        ]);
    });

    // Get popular articles with agency details
    Route::get('/popular', function (Request $request) {
        $articles = NewsArticle::with(['agency' => function($q) {
            $q->select('id', 'name', 'logo', 'status');
        }])->active()
            ->popular()
            ->limit($request->get('limit', 10))
            ->get();

        $transformedArticles = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'image_url' => $article->image_url,
                'is_featured' => $article->is_featured,
                'is_for_mehsana' => $article->is_for_mehsana,
                'visitor' => $article->visitor,
                'created_at' => $article->created_at,
                'agency' => [
                    'id' => $article->agency->id,
                    'name' => $article->agency->name,
                    'logo_url' => $article->agency->logo_url,
                    'initial' => $article->agency->initial
                ]
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $transformedArticles
        ]);
    });

    // Get specific article by ID and increment visitor count
    Route::get('/{id}', function ($id) {
        $article = NewsArticle::with(['agency' => function($q) {
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

        $transformedArticle = [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'image' => $article->image,
            'image_url' => $article->image_url,
            'is_active' => $article->is_active,
            'is_featured' => $article->is_featured,
            'is_for_mehsana' => $article->is_for_mehsana,
            'visitor' => $article->visitor + 1, // Include incremented count
            'created_at' => $article->created_at,
            'updated_at' => $article->updated_at,
            'agency' => [
                'id' => $article->agency->id,
                'name' => $article->agency->name,
                'logo' => $article->agency->logo,
                'logo_url' => $article->agency->logo_url,
                'initial' => $article->agency->initial,
                'is_active' => $article->agency->is_active
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => $transformedArticle
        ]);
    });

    // Get article by slug and increment visitor count
    Route::get('/slug/{slug}', function ($slug) {
        $article = NewsArticle::with(['agency' => function($q) {
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

        $transformedArticle = [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'image' => $article->image,
            'image_url' => $article->image_url,
            'is_active' => $article->is_active,
            'is_featured' => $article->is_featured,
            'is_for_mehsana' => $article->is_for_mehsana,
            'visitor' => $article->visitor + 1,
            'created_at' => $article->created_at,
            'updated_at' => $article->updated_at,
            'agency' => [
                'id' => $article->agency->id,
                'name' => $article->agency->name,
                'logo' => $article->agency->logo,
                'logo_url' => $article->agency->logo_url,
                'initial' => $article->agency->initial,
                'is_active' => $article->agency->is_active
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => $transformedArticle
        ]);
    });

    // Search articles with agency details
    Route::get('/search/{query}', function ($query, Request $request) {
        $articlesQuery = NewsArticle::with(['agency' => function($q) {
            $q->select('id', 'name', 'logo', 'status');
        }])->active()->search($query);
        
        // Add sorting to search results
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
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'image_url' => $article->image_url,
                'is_featured' => $article->is_featured,
                'is_for_mehsana' => $article->is_for_mehsana,
                'visitor' => $article->visitor,
                'created_at' => $article->created_at,
                'agency' => [
                    'id' => $article->agency->id,
                    'name' => $article->agency->name,
                    'logo_url' => $article->agency->logo_url,
                    'initial' => $article->agency->initial
                ]
            ];
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
    });

    // Get articles by category (Mehsana or General) with agency details
    Route::get('/category/{type}', function ($type, Request $request) {
        $isMehsana = strtolower($type) === 'mehsana';
        
        $query = NewsArticle::with(['agency' => function($q) {
            $q->select('id', 'name', 'logo', 'status');
        }])->active()->where('is_for_mehsana', $isMehsana);
        
        // Add sorting
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
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'image_url' => $article->image_url,
                'is_featured' => $article->is_featured,
                'is_for_mehsana' => $article->is_for_mehsana,
                'visitor' => $article->visitor,
                'created_at' => $article->created_at,
                'agency' => [
                    'id' => $article->agency->id,
                    'name' => $article->agency->name,
                    'logo_url' => $article->agency->logo_url,
                    'initial' => $article->agency->initial,
                    'is_active' => $article->agency->is_active
                ]
            ];
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
    });

    // Get all agencies (for mobile app reference)
    Route::get('/agencies', function () {
        $agencies = \App\Models\NewsAgency::active()
            ->select('id', 'name', 'logo', 'status')
            ->get()
            ->map(function ($agency) {
                return [
                    'id' => $agency->id,
                    'name' => $agency->name,
                    'logo' => $agency->logo,
                    'logo_url' => $agency->logo_url,
                    'initial' => $agency->initial,
                    'is_active' => $agency->is_active
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $agencies
        ]);
    });

    // Get articles by specific agency
    Route::get('/agency/{agencyId}', function ($agencyId, Request $request) {
        $query = NewsArticle::with(['agency' => function($q) {
            $q->select('id', 'name', 'logo', 'status');
        }])->active()->where('agency_id', $agencyId);
        
        // Add sorting
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
            return [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'image_url' => $article->image_url,
                'is_featured' => $article->is_featured,
                'is_for_mehsana' => $article->is_for_mehsana,
                'visitor' => $article->visitor,
                'created_at' => $article->created_at,
                'agency' => [
                    'id' => $article->agency->id,
                    'name' => $article->agency->name,
                    'logo_url' => $article->agency->logo_url,
                    'initial' => $article->agency->initial
                ]
            ];
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
    });
});