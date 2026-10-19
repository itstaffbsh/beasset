<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\ProductHistory;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function show($token)
    {
        $product = Aset::where('url_token', $token)
            ->with(['department', 'classification', 'histories.sender', 'histories.receiver.office'])
            ->firstOrFail();

        $last = $product->histories->sortByDesc('created_at')->first();
        $currentHistory = ($last && $last->diterima_oleh) ? $last : null;

        return response()->json([
            'product' => $product,
            'currentHistory' => $currentHistory
        ]);
    }

    public function showSpecs($token)
    {
        $product = Aset::where('url_token', $token)->with(['classification'])->firstOrFail();
        return response()->json(['product' => $product]);
    }

    public function showOwner($token)
    {
        $product = Aset::where('url_token', $token)
            ->with(['department', 'histories.receiver.office'])
            ->firstOrFail();

        $currentHistory = $product->histories->sortByDesc('created_at')->first();
        return response()->json([
            'product' => $product,
            'currentHistory' => $currentHistory
        ]);
    }

    public function apiShow($token)
    {
        $product = Aset::where('url_token', $token)
            ->with(['department', 'classification', 'histories.sender', 'histories.receiver.office'])
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data'   => $product
        ]);
    }

    public function update(Request $request, $token)
    {
        $product = Aset::where('url_token', $token)->firstOrFail();

        $request->validate([
            'pemilik_kantor' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
        ]);

        ProductHistory::create([
            'product_id' => $product->id,
            'dioper_oleh' => auth()->id(),
            'diterima_oleh' => null,
            'lokasi_baru' => $request->lokasi,
        ]);

        $product->update([
            'pemilik_kantor' => $request->pemilik_kantor,
            'lokasi' => $request->lokasi,
        ]);

        return response()->json(['message' => 'Data produk berhasil diupdate.']);
    }
}
