<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles with user counts.
     */
    public function index()
    {
        $roles = Role::withCount('users')
            ->orderBy('is_system', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'roles' => $roles,
            'menus' => Role::getMenuDefinitions(),
        ]);
    }

    /**
     * Get available menu definitions and their allowed actions.
     */
    public function menus()
    {
        return response()->json([
            'menus' => Role::getMenuDefinitions(),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'display_name' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array',
        ]);

        $rawName = trim($validated['name']);
        // Format slug: e.g. "Staff Gudang" => "staff_gudang"
        $slug = Str::slug($rawName, '_');

        // Check uniqueness of slug
        if (Role::where('name', $slug)->exists()) {
            return response()->json([
                'message' => 'Role dengan nama ini sudah ada.',
                'errors' => ['name' => ['Nama role sudah terdaftar.']]
            ], 422);
        }

        $role = Role::create([
            'name' => $slug,
            'display_name' => $validated['display_name'] ?: $rawName,
            'description' => $validated['description'] ?? null,
            'permissions' => $validated['permissions'],
            'is_system' => false,
        ]);

        return response()->json([
            'message' => 'Role berhasil ditambahkan.',
            'role' => $role->loadCount('users'),
        ], 201);
    }

    /**
     * Display the specified role.
     */
    public function show(string $id)
    {
        $role = Role::withCount('users')->findOrFail($id);

        return response()->json([
            'role' => $role,
            'menus' => Role::getMenuDefinitions(),
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'display_name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'permissions' => 'required|array',
        ]);

        // Don't allow clearing all permissions of super_admin
        if ($role->name === 'super_admin') {
            $validated['permissions'] = Role::getAllPermissionsList();
        }

        $role->display_name = $validated['display_name'];
        if (isset($validated['description'])) {
            $role->description = $validated['description'];
        }
        $role->permissions = $validated['permissions'];
        $role->save();

        return response()->json([
            'message' => 'Role berhasil diperbarui.',
            'role' => $role->loadCount('users'),
        ]);
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_system || in_array($role->name, ['super_admin', 'admin', 'user'])) {
            return response()->json([
                'message' => 'Role bawaan sistem (' . ($role->display_name ?: $role->name) . ') tidak dapat dihapus.'
            ], 422);
        }

        $usersCount = User::where('role', $role->name)->count();
        if ($usersCount > 0) {
            return response()->json([
                'message' => "Role ini tidak dapat dihapus karena sedang digunakan oleh {$usersCount} karyawan. Harap alihkan role karyawan tersebut terlebih dahulu."
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role berhasil dihapus.'
        ]);
    }
}
