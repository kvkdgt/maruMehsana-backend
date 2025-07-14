<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessEnquiryController;
use App\Http\Controllers\FactsController;
use App\Http\Controllers\TouristPlaceController;
use App\Http\Controllers\BannerAdController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\NewsAgencyController;
use App\Http\Controllers\AgencyAuthController;
use App\Http\Controllers\NewsCategoryController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('landing');
});
Route::get('/business/{businessId}', [ShareController::class, 'business']);
Route::get('/place/{placeId}', [ShareController::class, 'place']);
Route::get('/News/{newsId}', [ShareController::class, 'news']);



Route::get('/privacy-policy', function () {
    return view('privacy');
});
Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login']);
Route::middleware(['is_admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/tourist-places', [AdminController::class, 'touristPlaces'])->name('admin.tourist-places');

    Route::post('admin/categories/store', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('admin/categories/edit/{id}', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::post('admin/categories/update', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('admin/categories/delete/{id}', [CategoryController::class, 'destroy'])->name('categories.delete');

    Route::get('/admin/businesses', [AdminController::class, 'businesses'])->name('admin.businesses');
    Route::get('/admin/businesses/create', [BusinessController::class, 'createView'])->name('business.create');
    Route::post('/admin/businesses/store', [BusinessController::class, 'store'])->name('business.store');
    Route::delete('admin/businesses/delete/{id}', [BusinessController::class, 'destroy'])->name('business.delete');
    Route::get('admin/businesses/edit/{id}', [BusinessController::class, 'getBusinessById'])->name('business.edit');
    Route::delete('/admin/business-image/{id}', [BusinessController::class, 'deleteImage'])->name('business.image.delete');
    Route::post('/admin/businesses/update', [BusinessController::class, 'update'])->name('business.update');


    Route::get('/admin/facts', [AdminController::class, 'facts'])->name('admin.facts');
    Route::get('/admin/marketing', [AdminController::class, 'marketing'])->name('admin.marketing');

    Route::get('/admin/marketing/banner-ads', [AdminController::class, 'bannerAds'])->name('admin.banner-ads');


    Route::get('/admin/business-enquiry', [AdminController::class, 'BusinessEnquiry'])->name('admin.business-enquiry');

    Route::post('admin/facts/store', [FactsController::class, 'store'])->name('facts.store');
    Route::delete('admin/facts/delete/{id}', [FactsController::class, 'destroy'])->name('facts.delete');

    Route::post('admin/tourist-place/store', [TouristPlaceController::class, 'store'])->name('tourist_place.store');
    Route::delete('admin/tourist-places/delete/{id}', [TouristPlaceController::class, 'destroy'])->name('tourist_place.delete');

    Route::put('/admin/business-enquiry/update/{id}', [BusinessEnquiryController::class, 'updateStatus'])->name('admin.BusinessEnquery.updateStatus');


    Route::post('banner-ads/store', [BannerAdController::class, 'store'])->name('admin.banner-ads.store');
    Route::delete('/banner-ads/{bannerAd}', [BannerAdController::class, 'destroy'])->name('admin.banner-ads.destroy');
    Route::patch('/admin/banner-ads/updateStatus/{id}', [BannerAdController::class, 'updateStatus']);
    Route::get('/admin/marketing/notifications', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('admin.notifications');
    Route::post('/admin/notifications/store', [App\Http\Controllers\Admin\NotificationController::class, 'store'])->name('notifications.store');
    Route::post('/admin/notifications/send-now/{id}', [App\Http\Controllers\Admin\NotificationController::class, 'sendNow'])->name('notifications.send-now');
    Route::delete('/admin/notifications/delete/{id}', [App\Http\Controllers\Admin\NotificationController::class, 'delete'])->name('notifications.delete');

    Route::get('/admin/notifications/{id}/logs', [App\Http\Controllers\Admin\NotificationController::class, 'showLogs'])->name('admin.notifications.logs');

    Route::get('/admin/news-agencies', [App\Http\Controllers\NewsAgencyController::class, 'index'])->name('admin.news-agencies');
    Route::get('/admin/news-agencies/create', [App\Http\Controllers\NewsAgencyController::class, 'create'])->name('admin.news-agencies.create');
    Route::post('/admin/news-agencies/store', [App\Http\Controllers\NewsAgencyController::class, 'store'])->name('admin.news-agencies.store');
    Route::get('/admin/news-agencies/edit/{id}', [App\Http\Controllers\NewsAgencyController::class, 'edit'])->name('admin.news-agencies.edit');
    Route::put('/admin/news-agencies/update/{id}', [App\Http\Controllers\NewsAgencyController::class, 'update'])->name('admin.news-agencies.update');
    Route::delete('/admin/news-agencies/delete/{id}', [App\Http\Controllers\NewsAgencyController::class, 'destroy'])->name('admin.news-agencies.destroy');
    Route::post('/admin/news-agencies/{id}/toggle-status', [App\Http\Controllers\NewsAgencyController::class, 'toggleStatus'])->name('admin.news-agencies.toggle-status');



Route::get('/admin/news-categories', [NewsCategoryController::class, 'index'])->name('admin.news-categories');
    Route::post('/admin/news-categories/store', [NewsCategoryController::class, 'store'])->name('news-categories.store');
    Route::get('/admin/news-categories/edit/{id}', [NewsCategoryController::class, 'edit'])->name('news-categories.edit');
    Route::post('/admin/news-categories/update', [NewsCategoryController::class, 'update'])->name('news-categories.update');
    Route::delete('/admin/news-categories/delete/{id}', [NewsCategoryController::class, 'destroy'])->name('news-categories.delete');
    Route::post('/admin/news-categories/toggle-status/{id}', [NewsCategoryController::class, 'toggleStatus'])->name('news-categories.toggle-status');





    
});
Route::prefix('agency')->name('agency.')->group(function () {

        // Guest routes (only accessible when not logged in)
        Route::middleware('guest:agency')->group(function () {
            Route::get('/login', [AgencyAuthController::class, 'showLoginForm'])->name('login');
            Route::post('/login', [AgencyAuthController::class, 'login'])->name('login.submit');
        });

        // Protected routes (only accessible when logged in as agency admin)
        Route::middleware(['agency.auth'])->group(function () {

            // Dashboard
            Route::get('/dashboard', [AgencyAuthController::class, 'dashboard'])->name('dashboard');

            // Profile Management
            Route::get('/profile', [AgencyAuthController::class, 'profile'])->name('profile');
            Route::put('/profile', [AgencyAuthController::class, 'updateProfile'])->name('profile.update');

            // Logout
            Route::post('/logout', [AgencyAuthController::class, 'logout'])->name('logout');

            // API Routes for checking authentication
            Route::get('/check-auth', [AgencyAuthController::class, 'checkAuth'])->name('check.auth');
    Route::resource('news', App\Http\Controllers\Agency\NewsArticleController::class);
Route::post('/upload-image', [App\Http\Controllers\Agency\NewsArticleController::class, 'uploadImage'])->name('upload.image');
            // Add more protected routes here as needed
            // Example:
            // Route::resource('articles', AgencyArticleController::class);
            // Route::resource('categories', AgencyCategoryController::class);
        });
    });

    // Optional: Redirect root agency URL to login
    Route::get('/agency', function () {
        return redirect()->route('agency.login');
    });
