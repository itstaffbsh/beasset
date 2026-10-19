<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use Illuminate\Http\Request;

class ClassificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Classification::query();
        $searchTerm = $request->get('q');
        if ($searchTerm) {
            $query->where('nama_klasifikasi', 'like', "%{$searchTerm}%");
        }

        $sortBy = $request->get('sort_by', 'id');
        $sortDir = $request->get('sort_dir', 'desc');
        $perPage = (int) $request->get('per_page', 10);

        if (in_array($sortBy, ['nama_klasifikasi']) || \Illuminate\Support\Facades\Schema::hasColumn('classifications', $sortBy)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($query->paginate($perPage));
        }
        
        $classifications = $query->get();
        return view('classifications.index', compact('classifications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_klasifikasi' => 'required|string|max:255',
        ]);
        
        $classification = Classification::create($request->all());
        
        return response()->json([
            'message' => 'Klasifikasi berhasil ditambahkan.',
            'data' => $classification
        ], 201);
    }

    public function show(Classification $classification)
    {
        return response()->json($classification);
    }

    public function update(Request $request, Classification $classification)
    {
        $request->validate([
            'nama_klasifikasi' => 'required|string|max:255',
        ]);
        
        $classification->update($request->all());
        
        return response()->json([
            'message' => 'Klasifikasi berhasil diupdate.',
            'data' => $classification
        ]);
    }

    public function destroy(Classification $classification)
    {
        $classification->delete();
        return response()->json(['message' => 'Klasifikasi berhasil dihapus.']);
    }
}
