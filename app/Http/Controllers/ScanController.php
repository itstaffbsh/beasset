<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\ProductHistory;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    /**
     * Resolves product from URL token, full nomor unik (e.g. IT-00001), nomor unik, or device name.
     */
    protected function findProduct(string $token): Aset
    {
        $token = trim($token);

        // 1. Direct url_token match
        $product = Aset::where('url_token', $token)->first();
        if ($product) return $product;

        // 2. Full nomor unik match (e.g. "IT-00001" or "HR-00012")
        if (str_contains($token, '-')) {
            $parts = explode('-', $token, 2);
            $code = $parts[0];
            $num = $parts[1];
            $product = Aset::where('nomor_unik', $num)
                ->whereHas('department', function ($q) use ($code) {
                    $q->where('kode_asset', $code);
                })->first();
            if ($product) return $product;
        }

        // 3. Direct nomor_unik, device_name, or uuid id match
        $product = Aset::where('nomor_unik', $token)
            ->orWhere('device_name', $token)
            ->orWhere('id', $token)
            ->first();

        if ($product) return $product;

        abort(404, 'Aset tidak ditemukan.');
    }

    public function show($token)
    {
        $product = $this->findProduct($token)
            ->load(['department', 'classification', 'histories.sender', 'histories.receiver.office']);

        $last = $product->histories->sortByDesc('created_at')->first();
        $currentHistory = ($last && $last->diterima_oleh) ? $last : null;

        return response()->json([
            'product' => $product,
            'currentHistory' => $currentHistory
        ]);
    }

    public function showSpecs($token)
    {
        $product = $this->findProduct($token)->load(['classification']);
        return response()->json(['product' => $product]);
    }

    public function showOwner($token)
    {
        $product = $this->findProduct($token)
            ->load(['department', 'histories.receiver.office']);

        $currentHistory = $product->histories->sortByDesc('created_at')->first();
        return response()->json([
            'product' => $product,
            'currentHistory' => $currentHistory
        ]);
    }

    public function apiShow($token)
    {
        $product = $this->findProduct($token)
            ->load(['department', 'classification', 'histories.sender', 'histories.receiver.office']);

        return response()->json([
            'status' => 'success',
            'data'   => $product
        ]);
    }

    public function update(Request $request, $token)
    {
        $product = $this->findProduct($token);

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
