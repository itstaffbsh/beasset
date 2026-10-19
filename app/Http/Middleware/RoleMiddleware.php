<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /* | [FUNGSI UTAMA] | 
       | Kegunaan: Mengecek apakah pengguna memiliki hak akses (role) yang tepat.
       | Cara kerja: Memeriksa kolom 'role' di tabel users dan mencocokkannya dengan aturan di web.php.
    */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Jika belum login ATAU role user tidak ada dalam daftar yang diizinkan (...$roles)
        if (!auth()->check() || !in_array(auth()->user()->role, $roles)) {
            // Maka hentikan akses dan tampilkan pesan error 403
            abort(403, 'Maaf, Anda tidak memiliki akses ke halaman ini.');
        }

        // Jika lolos pengecekan, lanjutkan ke halaman yang dituju
        return $next($request);
    }
}
