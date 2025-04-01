<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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
