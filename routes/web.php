<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\FactsController;

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
    return view('welcome');
});

Route::get('/admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login']);
Route::middleware([ 'is_admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
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
    Route::post('admin/facts/store', [FactsController::class, 'store'])->name('facts.store');
    Route::delete('admin/facts/delete/{id}', [FactsController::class, 'destroy'])->name('facts.delete');


   



});