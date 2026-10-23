<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
Route::get('/storage/{path}', function (\Illuminate\Http\Request $request, string $path) {
    $disk = Storage::disk('public');
    
    if (!$disk->exists($path)) {
        return response()->json([
            'status'  => 'error',
            'message' => 'File tidak ditemukan di storage server.'
        ], 404);
    }

    $mime = $disk->mimeType($path) ?: 'application/octet-stream';
    $filename = basename($path);
    $disposition = $request->has('download') ? 'attachment' : 'inline';

    return $disk->response($path, $filename, [
        'Content-Type'        => $mime,
        'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        'Access-Control-Allow-Origin' => '*',
    ]);
})->where('path', '.*')->name('public.storage');

// Fallback untuk setiap permintaan web yang tidak terdaftar
Route::fallback(function () {
    return response()->json([
        'status'  => 'error',
        'message' => 'Resource not found. This server is configured as a REST API only.',
    ], 404);
});
