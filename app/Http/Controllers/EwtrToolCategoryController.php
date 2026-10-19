<?php

namespace App\Http\Controllers;

use App\Models\EwtrToolCategory;
use Illuminate\Http\Request;

class EwtrToolCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = EwtrToolCategory::query();

        $searchTerm = $request->get('q');
        if ($searchTerm) {
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        if (in_array($sortBy, ['id', 'name', 'created_at'])) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($request->boolean('all')) {
            return response()->json($query->get());
        }

        $perPage = (int) $request->get('per_page', 10);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = EwtrToolCategory::create([
            'name' => trim($request->name),
        ]);

        return response()->json([
            'message' => 'Kategori Tools EWTR berhasil ditambahkan.',
            'data' => $category,
        ], 201);
    }

    public function show(EwtrToolCategory $ewtrCategory)
    {
        return response()->json($ewtrCategory);
    }

    public function update(Request $request, EwtrToolCategory $ewtrCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $ewtrCategory->update([
            'name' => trim($request->name),
        ]);

        return response()->json([
            'message' => 'Kategori Tools EWTR berhasil diperbarui.',
            'data' => $ewtrCategory,
        ]);
    }

    public function destroy(EwtrToolCategory $ewtrCategory)
    {
        $ewtrCategory->delete();
        return response()->json([
            'message' => 'Kategori Tools EWTR berhasil dihapus.',
        ]);
    }
}
