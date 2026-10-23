<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicStorageController;

/*
|--------------------------------------------------------------------------
| Web Routes (Pure REST API & Public Storage Serving)
|--------------------------------------------------------------------------
| Seluruh antarmuka / UI aplikasi dijalankan melalui Nuxt.js Frontend.
| Laravel backend ini dikhususkan 100% murni melayani REST API.
| Semua endpoint API terdaftar di routes/api.php.
*/

Route::get('/', function () {
    return response()->json([
        'status'  => 'online',
        'app'     => config('app.name', 'Asset Management API'),
        'type'    => 'REST API Server',
        'message' => 'Backend REST API is running. User Interface is served by Nuxt.js frontend.',
    ]);
});

// Endpoint untuk menyajikan file publik storage (PDF, EWTR, Avatar, Scan, dll)
Route::match(['GET', 'HEAD', 'OPTIONS'], '/storage/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.*')
    ->name('public.storage');

// Fallback untuk setiap permintaan web yang tidak terdaftar
Route::fallback(function () {
    return response()->json([
        'status'  => 'error',
        'message' => 'Resource not found. This server is configured as a REST API only.',
    ], 404);
});
