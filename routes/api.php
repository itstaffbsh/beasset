<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AsetController;
use App\Http\Controllers\BorrowStatusController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\EwtrToolCategoryController;
use App\Http\Controllers\EwtrController;
use App\Http\Controllers\RoleController;

// Autentikasi
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index']);
    

    // Profile
    Route::get('/profile', [AuthController::class, 'me']);
    Route::patch('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/password', [AuthController::class, 'updatePassword']);
    Route::post('/profile/photo', [AuthController::class, 'uploadPhoto']);
    
    
    // Super Admin Routes
    Route::middleware('role:super_admin')->group(function () {
        Route::post('/users/{user}/resign', [UserController::class, 'resign']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        
        Route::get('/products-trash', [AsetController::class, 'trashIndex']);
        Route::post('/products-trash/{id}/restore', [AsetController::class, 'restore']);
        Route::delete('/products-trash/{id}/force', [AsetController::class, 'forceDelete']);
    });

    // Admin & Super Admin Routes
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/users/{user}/details', [UserController::class, 'details']);
        Route::apiResource('users', UserController::class)->except(['destroy', 'show']);
        Route::get('/users-export', [UserController::class, 'export']);
        Route::get('/users-export-pdf', [UserController::class, 'exportPdf']);
        Route::post('/users-import', [UserController::class, 'import']);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
        Route::post('/users/{user}/make-admin', [UserController::class, 'makeAdmin']);

        // Role Management Routes
        Route::get('/roles-menus', [RoleController::class, 'menus']);
        Route::apiResource('roles', RoleController::class);

        Route::apiResource('offices', OfficeController::class);
        Route::apiResource('classifications', ClassificationController::class);
        Route::apiResource('departments', DepartmentController::class);
        Route::apiResource('ewtr-categories', EwtrToolCategoryController::class);
        Route::apiResource('ewtrs', EwtrController::class);
        Route::post('/ewtrs/{ewtr}/items/{item}/toggle', [EwtrController::class, 'toggleItemStatus']);
        Route::apiResource('products', AsetController::class);
        Route::get('/products/{product}/qr', [AsetController::class, 'printQr']);
        
        Route::get('/products-export', [AsetController::class, 'export']);
        Route::get('/products-export-pdf', [AsetController::class, 'exportPdf']);
        Route::post('/products-import', [AsetController::class, 'import']);
        Route::get('/products/next-nomor/{department_id}', [AsetController::class, 'getNextNomor']);
        
        Route::get('/history', [AsetController::class, 'historyIndex']);
        
        // Status Peminjaman
        Route::get('/borrow-status', [BorrowStatusController::class, 'index']);
        Route::post('/borrow-status/{id}/upload-scan', [BorrowStatusController::class, 'uploadScan']);
        Route::get('/history/{productHistory}', [AsetController::class, 'showHistory']);
        Route::put('/history/{productHistory}', [AsetController::class, 'updateHistory']);
        Route::post('/history/{productHistory}/quick-return', [AsetController::class, 'quickReturn']);
        Route::delete('/history/{productHistory}/cancel', [AsetController::class, 'cancelHistory'])->middleware('role:super_admin');

        Route::post('/transfer/store', [AsetController::class, 'storeTransfer']);
        Route::get('/sttb/{batch_id}', [AsetController::class, 'showSttb']);
    });

    // Scan update
    Route::post('/scan/{token}/update', [ScanController::class, 'update']);
});

// Publik Scan API
Route::get('/scan/{token}', [ScanController::class, 'apiShow']);

// Publik Storage API
Route::match(['GET', 'HEAD', 'OPTIONS'], '/storage/{path}', [\App\Http\Controllers\PublicStorageController::class, 'show'])->where('path', '.*');
