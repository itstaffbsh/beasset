<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index(Request $request)
    {
        $query = Office::query();
        $searchTerm = $request->get('q');
        if ($searchTerm) {
            $query->where('nama_kantor', 'like', "%{$searchTerm}%")
                  ->orWhere('alamat', 'like', "%{$searchTerm}%");
        }

        $sortBy = $request->get('sort_by', 'id');
        $sortDir = $request->get('sort_dir', 'desc');
        $perPage = (int) $request->get('per_page', 10);

        if (in_array($sortBy, ['nama_kantor', 'alamat']) || \Illuminate\Support\Facades\Schema::hasColumn('offices', $sortBy)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kantor' => 'required|string|max:255',
            'alamat' => 'nullable|string',
        ]);
        
        $office = Office::create($request->all());
        
        return response()->json([
            'message' => 'Kantor berhasil ditambahkan.',
            'data' => $office
        ], 201);
    }

    public function show(Office $office)
    {
        return response()->json($office);
    }

    public function update(Request $request, Office $office)
    {
        $request->validate([
            'nama_kantor' => 'required|string|max:255',
            'alamat' => 'nullable|string',
        ]);
        
        $office->update($request->all());
        
        return response()->json([
            'message' => 'Kantor berhasil diupdate.',
            'data' => $office
        ]);
    }

    public function destroy(Office $office)
    {
        $office->delete();
        return response()->json(['message' => 'Kantor berhasil dihapus.']);
    }
}
