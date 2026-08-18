<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\AssetCategoryController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetLocationController;
use App\Http\Controllers\AssetMaintenanceController;
use App\Http\Controllers\AssetReportController;
use App\Http\Controllers\AssetStatusController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'home' : 'login');
});

/*
|--------------------------------------------------------------------------
| Guest (authentication) routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated application routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {

    // Temporary landing page until the real Dashboard is built in Week 6.
    Route::get('home', [HomeController::class, 'index'])->name('home');

    // Asset Registration (Administrator, Asset Officer)
    Route::middleware('role:administrator,asset_officer')->group(function () {
        Route::get('assets/create', [AssetController::class, 'create'])->name('assets.create');
        Route::post('assets', [AssetController::class, 'store'])->name('assets.store');
        Route::get('assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('assets/{asset}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
    });

    // Search & Filter (all authenticated roles)
    Route::get('assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('assets/{asset}/qr', [AssetController::class, 'qr'])->name('assets.qr');
    Route::get('assets/{asset}', [AssetController::class, 'show'])->name('assets.show');

    // Asset Assignment (Administrator, Asset Officer)
    Route::middleware('role:administrator,asset_officer')->group(function () {
        Route::get('assignments', [AssetAssignmentController::class, 'index'])->name('assignments.index');
        Route::post('assignments', [AssetAssignmentController::class, 'store'])->name('assignments.store');
        Route::put('assignments/{assignment}', [AssetAssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('assignments/{assignment}', [AssetAssignmentController::class, 'destroy'])->name('assignments.destroy');
    });

    // Asset Maintenance (Administrator, Asset Officer)
    Route::middleware('role:administrator,asset_officer')->group(function () {
        Route::get('maintenance', [AssetMaintenanceController::class, 'index'])->name('maintenance.index');
        Route::post('maintenance', [AssetMaintenanceController::class, 'store'])->name('maintenance.store');
        Route::put('maintenance/{maintenance}', [AssetMaintenanceController::class, 'update'])->name('maintenance.update');
        Route::delete('maintenance/{maintenance}', [AssetMaintenanceController::class, 'destroy'])->name('maintenance.destroy');
    });

    // Asset Report (all authenticated roles)
    Route::get('reports', [AssetReportController::class, 'index'])->name('reports.index');

    // Account Settings (all authenticated roles)
    Route::get('settings', [AccountSettingsController::class, 'index'])->name('settings.index');
    Route::put('settings/profile', [AccountSettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('settings/password', [AccountSettingsController::class, 'updatePassword'])->name('settings.password.update');

    // Asset Management settings — Category, Location, Status (Administrator, Asset Officer)
    Route::middleware('role:administrator,asset_officer')->group(function () {
        Route::get('asset-management', [SettingsController::class, 'index'])->name('asset-management.index');

        Route::get('categories', [AssetCategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [AssetCategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [AssetCategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [AssetCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('locations', [AssetLocationController::class, 'index'])->name('locations.index');
        Route::post('locations', [AssetLocationController::class, 'store'])->name('locations.store');
        Route::put('locations/{location}', [AssetLocationController::class, 'update'])->name('locations.update');
        Route::delete('locations/{location}', [AssetLocationController::class, 'destroy'])->name('locations.destroy');

        Route::post('statuses', [AssetStatusController::class, 'store'])->name('statuses.store');
        Route::put('statuses/{status}', [AssetStatusController::class, 'update'])->name('statuses.update');
        Route::delete('statuses/{status}', [AssetStatusController::class, 'destroy'])->name('statuses.destroy');
    });

    // User Management (Administrator only)
    Route::middleware('role:administrator')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
