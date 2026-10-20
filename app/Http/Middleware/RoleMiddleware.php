<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            abort(401, 'Unauthenticated.');
        }

        $user = auth()->user();
        $userRole = $user->role;

        // 1. Super Admin selalu memiliki akses ke seluruh resource
        if ($userRole === 'super_admin') {
            return $next($request);
        }

        // 2. Jika rute hanya khusus super_admin (misal: 'role:super_admin')
        if (count($roles) === 1 && $roles[0] === 'super_admin') {
            abort(403, 'Akses khusus Super Admin.');
        }

        // 3. Admin memiliki akses penuh ke fitur operasional
        if ($userRole === 'admin') {
            return $next($request);
        }

        // 4. Jika role user secara eksplisit diizinkan di parameter middleware (misal: 'role:user')
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // 5. Otorisasi Dinamis Berdasarkan Hak Akses / Permissions Role User
        $userPermissions = $user->permissions ?? [];
        if ($this->hasPermissionForRequest($request, $userPermissions)) {
            return $next($request);
        }

        abort(403, 'Maaf, Anda tidak memiliki hak akses ke halaman/data ini.');
    }

    /**
     * Memeriksa apakah user memiliki hak akses (permissions) sesuai method dan endpoint yang diakses.
     */
    private function hasPermissionForRequest(Request $request, array $permissions): bool
    {
        $path = trim($request->path(), '/');
        if (str_starts_with($path, 'api/')) {
            $path = substr($path, 4);
        }
        $segments = explode('/', $path);
        $resource = strtolower($segments[0] ?? '');

        // Mapping endpoint resource ke key menu di role permissions
        $resourceMenuMap = [
            'products'            => 'products',
            'products-export'     => 'products',
            'products-export-pdf' => 'products',
            'products-import'     => 'products',
            'transfer'            => 'transfer',
            'sttb'                => 'transfer', // STTB merupakan dokumen hasil transaksi serah terima
            'history'             => 'history',
            'borrow-status'       => 'borrow-status',
            'users'               => 'users',
            'users-export'        => 'users',
            'users-export-pdf'    => 'users',
            'users-import'        => 'users',
            'offices'             => 'offices',
            'departments'         => 'departments',
            'classifications'     => 'classifications',
            'ewtr-categories'     => 'ewtr-categories',
            'ewtrs'               => 'ewtr',
            'dashboard'           => 'dashboard',
            'roles'               => 'roles',
            'roles-menus'         => 'roles',
        ];

        if (!isset($resourceMenuMap[$resource])) {
            return false;
        }

        $menu = $resourceMenuMap[$resource];
        $menuPerms = (array) ($permissions[$menu] ?? []);

        // Jika user sama sekali tidak punya permission di menu ini
        if (empty($menuPerms)) {
            return false;
        }

        $method = strtoupper($request->method());

        // Cek aksi Export
        if (str_contains($resource, 'export')) {
            return in_array('export', $menuPerms);
        }

        // Cek aksi Import
        if (str_contains($resource, 'import')) {
            return in_array('import', $menuPerms);
        }

        // Cek aksi Jadikan Admin & Reset Password pada karyawan (users)
        if ($resource === 'users') {
            $subAction = strtolower($segments[2] ?? '');
            if ($subAction === 'make-admin') {
                return in_array('make_admin', $menuPerms);
            }
            if ($subAction === 'reset-password') {
                return in_array('reset_password', $menuPerms);
            }
        }

        // Cek aksi View (GET / HEAD)
        if ($method === 'GET' || $method === 'HEAD') {
            return in_array('view', $menuPerms);
        }

        // Cek aksi Tambah (POST)
        if ($method === 'POST') {
            // Khusus upload scan di status peminjaman, izinkan jika punya hak edit atau tambah
            if ($resource === 'borrow-status') {
                return in_array('tambah', $menuPerms) || in_array('edit', $menuPerms);
            }
            return in_array('tambah', $menuPerms);
        }

        // Cek aksi Edit (PUT / PATCH)
        if ($method === 'PUT' || $method === 'PATCH') {
            return in_array('edit', $menuPerms);
        }

        // Cek aksi Delete (DELETE)
        if ($method === 'DELETE') {
            return in_array('delete', $menuPerms);
        }

        return false;
    }
}
