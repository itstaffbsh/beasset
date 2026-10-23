<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query();
        $searchTerm = $request->get('q');
        if ($searchTerm) {
            $query->where('nama_departemen', 'like', "%{$searchTerm}%")
                  ->orWhere('kode_asset', 'like', "%{$searchTerm}%");
        }

        $sortBy = $request->get('sort_by', 'id');
        $sortDir = $request->get('sort_dir', 'desc');
        $perPage = (int) $request->get('per_page', 10);

        if (in_array($sortBy, ['nama_departemen', 'kode_asset']) || \Illuminate\Support\Facades\Schema::hasColumn('departments', $sortBy)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_departemen' => 'required|string|max:255',
            'kode_asset' => 'required|string|max:10',
        ]);
        
        $department = Department::create($request->all());
        
        return response()->json([
            'message' => 'Departemen berhasil ditambahkan.',
            'data' => $department
        ], 201);
    }

    public function show(Department $department)
    {
        return response()->json($department);
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'nama_departemen' => 'required|string|max:255',
            'kode_asset' => 'required|string|max:10',
        ]);
        
        $department->update($request->all());
        
        return response()->json([
            'message' => 'Departemen berhasil diupdate.',
            'data' => $department
        ]);
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return response()->json(['message' => 'Departemen berhasil dihapus.']);
    }
}
