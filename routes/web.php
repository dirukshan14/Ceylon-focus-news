<?php

use App\Http\Controllers\Admin\AdministratorController;
use App\Http\Controllers\Admin\AdvertisementController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeathNoticeController as AdminDeathNoticeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\VideoController as AdminVideoController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\Auth\PublicAuthController;
use App\Http\Controllers\DeathNoticeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/news/{article:slug}', [ArticleController::class, 'show'])->name('news.show');
Route::get('/category/{category:slug}', [ArticleController::class, 'category'])->name('category.show');
Route::get('/tag/{tag:slug}', [ArticleController::class, 'tag'])->name('tag.show');
Route::get('/author/{author}', [ArticleController::class, 'author'])->name('author.show');

Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/{video:slug}', [VideoController::class, 'show'])->name('videos.show');

Route::get('/death-notices', [DeathNoticeController::class, 'index'])->name('death-notices.index');
Route::get('/death-notices/{deathNotice:slug}', [DeathNoticeController::class, 'show'])->name('death-notices.show');

Route::get('/search', [SearchController::class, 'index'])->middleware('throttle:60,1')->name('search');

Route::get('/ads/{advertisement}/click', [AdController::class, 'click'])->middleware('throttle:30,1')->name('ads.click');

/*
|--------------------------------------------------------------------------
| Public User Authentication (readers who want to comment/subscribe etc.)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [PublicAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [PublicAuthController::class, 'login'])->middleware('throttle:20,1');
    Route::get('/register', [PublicAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [PublicAuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [PublicAuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin Authentication (separate, secure /admin/login)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    // Deliberately NOT wrapped in the 'guest' middleware: that middleware
    // redirects ANY authenticated user away (readers included), which made
    // /admin/login unreachable for anyone who happened to have a public
    // reader session active. AdminLoginController::show() already handles
    // "redirect an already-logged-in admin to the dashboard" itself.
    Route::get('/login', [AdminLoginController::class, 'show'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->middleware('throttle:20,1');

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

        Route::get('/force-password-change', [ForcePasswordChangeController::class, 'show'])->name('password.force');
        Route::post('/force-password-change', [ForcePasswordChangeController::class, 'update'])->middleware('throttle:10,1');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Panel (protected by auth + EnsureAdminAccess + per-route permissions)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin.access'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // News / Articles
    Route::middleware('permission:manage-news')->group(function () {
        Route::resource('articles', AdminArticleController::class)->except(['show']);
    });

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

    // Videos
    Route::middleware('permission:manage-videos')->group(function () {
        Route::resource('videos', AdminVideoController::class)->except(['show']);
    });

    // Death Notices
    Route::middleware('permission:manage-death-notices')->group(function () {
        Route::resource('death-notices', AdminDeathNoticeController::class)
            ->parameters(['death-notices' => 'deathNotice'])
            ->except(['show']);
    });

    // Advertisements
    Route::middleware('permission:manage-advertisements')->group(function () {
        Route::resource('advertisements', AdvertisementController::class)->except(['show']);
        Route::post('advertisements/{advertisement}/toggle', [AdvertisementController::class, 'toggle'])
            ->name('advertisements.toggle');
    });

    // Social links
    Route::middleware('permission:manage-social-links')->group(function () {
        Route::resource('social-links', SocialLinkController::class)->except(['show', 'create', 'edit']);
    });

    // Settings
    Route::middleware('permission:manage-settings')->group(function () {
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    // Administrators (Super Admin only, enforced by manage-administrators permission)
    Route::middleware('permission:manage-administrators')->group(function () {
        Route::resource('administrators', AdministratorController::class)
            ->parameters(['administrators' => 'administrator'])
            ->except(['show']);
        Route::post('administrators/{administrator}/reset-password', [AdministratorController::class, 'resetPassword'])
            ->name('administrators.reset-password');
    });

    // Roles & Permissions (Super Admin only)
    Route::middleware('permission:manage-roles')->group(function () {
        Route::resource('roles', RoleController::class)->except(['show']);
    });
});
