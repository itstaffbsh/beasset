<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicStorageController extends Controller
{
    /**
     * Menyajikan file publik dari storage (PDF, scan STTB, avatar, EWTR, dll)
     * dengan dukungan byte-range streaming, CORS penuh, dan frame embedding.
     */
    public function show(Request $request, string $path)
    {
        // Bersihkan prefix jika ada
        $cleanPath = ltrim(preg_replace('/^\/?(public\/|storage\/)/', '', $path), '/');
        $disk = Storage::disk('public');

        if (!$disk->exists($cleanPath)) {
            // Cek file langsung di filesystem storage/app/public
            $physicalPath = storage_path('app/public/' . $cleanPath);
            if (!file_exists($physicalPath)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'File tidak ditemukan di storage server: ' . $cleanPath
                ], 404);
            }
            $fullPath = $physicalPath;
        } else {
            $fullPath = $disk->path($cleanPath);
        }

        $mime = $disk->mimeType($cleanPath) ?: 'application/octet-stream';
        $filename = basename($cleanPath);
        $disposition = $request->has('download') ? 'attachment' : 'inline';

        $response = response()->file($fullPath, [
            'Content-Type'                  => $mime,
            'Content-Disposition'           => $disposition . '; filename="' . $filename . '"',
            'Access-Control-Allow-Origin'   => '*',
            'Access-Control-Allow-Methods'  => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers'  => '*',
            'Access-Control-Expose-Headers' => 'Content-Disposition, Content-Length, Accept-Ranges',
            'Content-Security-Policy'       => 'frame-ancestors *;',
        ]);

        // Hapus X-Frame-Options agar iframe / embed di frontend Nuxt tidak di-block
        $response->headers->remove('X-Frame-Options');

        return $response;
    }
}
