<?php

namespace App\Http\Controllers;

use App\Models\ProductHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Rules\SafePdf;

class BorrowStatusController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type'); // meminjam_belum, meminjam_sukses, kembali_belum, kembali_sukses
        $searchTerm = $request->get('q');
        $perPage = $request->get('per_page', 10);

        $query = ProductHistory::with(['product', 'sender', 'receiver', 'pihakPertama'])->latest();

        if ($type === 'meminjam_belum') {
            $query->where('jenis_transaksi', 'meminjam')->whereNull('scanned_pdf');
        } elseif ($type === 'meminjam_sukses') {
            $query->where('jenis_transaksi', 'meminjam')->whereNotNull('scanned_pdf');
        } elseif ($type === 'kembali_belum') {
            $query->where('jenis_transaksi', 'mengembalikan')->whereNull('scanned_pdf');
        } elseif ($type === 'kembali_sukses') {
            $query->where('jenis_transaksi', 'mengembalikan')->whereNotNull('scanned_pdf');
        }

        if ($searchTerm) {
            $query->whereHas('product', function($q) use ($searchTerm) {
                $q->where('description', 'like', "%{$searchTerm}%")
                  ->orWhere('nomor_unik', 'like', "%{$searchTerm}%");
            });
        }

        $data = $query->paginate($perPage);

        return response()->json($data);
    }

    public function uploadScan(Request $request, $id)
    {
        $request->validate([
            'scanned_pdf' => ['required', 'file', 'max:10240', new SafePdf()]
        ]);

        $history = ProductHistory::findOrFail($id);

        try {
            if ($request->hasFile('scanned_pdf')) {
                // Delete old file if exists
                if ($history->scanned_pdf && Storage::disk('public')->exists($history->scanned_pdf)) {
                    Storage::disk('public')->delete($history->scanned_pdf);
                }

                $path = $request->file('scanned_pdf')->store('scanned_pdfs', 'public');
                $history->scanned_pdf = $path;
                $history->save();
            }
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Permission denied') || str_contains($msg, 'mkdir()')) {
                $msg .= '. Server VPS tidak memiliki izin tulis pada folder storage. Jalankan: sudo chown -R www-data:www-data storage bootstrap/cache && sudo chmod -R 775 storage bootstrap/cache';
            }
            return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $msg], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'File scan berhasil diupload',
            'data' => $history
        ]);
    }
}
