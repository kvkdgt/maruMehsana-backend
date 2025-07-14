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

Route::prefix('news')->group(function () {
    
    // Get all active news articles with pagination
    Route::get('/', function (Request $request) {
        $query = NewsArticle::active();
        
        // Add sorting options
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

        return response()->json([
            'status' => 'success',
            'data' => $articles->items(),
            'pagination' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
                'has_more' => $articles->hasMorePages()
            ]
        ]);
    });

    // Get featured articles
    Route::get('/featured', function () {
        $articles = NewsArticle::active()
            ->featured()
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $articles
        ]);
    });

    // Get popular articles
    Route::get('/popular', function (Request $request) {
        $articles = NewsArticle::active()
            ->popular()
            ->limit($request->get('limit', 10))
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $articles
        ]);
    });

    // Get specific article by ID and increment visitor count
    Route::get('/{id}', function ($id) {
        $article = NewsArticle::active()->find($id);

        if (!$article) {
            return response()->json([
                'status' => 'error',
                'message' => 'Article not found'
            ], 404);
        }

        // Increment visitor count
        $article->incrementVisitor();

        return response()->json([
            'status' => 'success',
            'data' => $article->fresh() // Get updated data with new visitor count
        ]);
    });

    // Get article by slug and increment visitor count
    Route::get('/slug/{slug}', function ($slug) {
        $article = NewsArticle::active()->where('slug', $slug)->first();

        if (!$article) {
            return response()->json([
                'status' => 'error',
                'message' => 'Article not found'
            ], 404);
        }

        // Increment visitor count
        $article->incrementVisitor();

        return response()->json([
            'status' => 'success',
            'data' => $article->fresh() // Get updated data with new visitor count
        ]);
    });

    // Search articles
    Route::get('/search/{query}', function ($query, Request $request) {
        $articlesQuery = NewsArticle::active()->search($query);
        
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

        return response()->json([
            'status' => 'success',
            'data' => $articles->items(),
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

    // Get articles by category (Mehsana or General)
    Route::get('/category/{type}', function ($type, Request $request) {
        $isMehsana = strtolower($type) === 'mehsana';
        
        $query = NewsArticle::active()->where('is_for_mehsana', $isMehsana);
        
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

        return response()->json([
            'status' => 'success',
            'data' => $articles->items(),
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
});