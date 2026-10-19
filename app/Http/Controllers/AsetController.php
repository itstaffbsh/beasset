<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use App\Models\Department;
use App\Models\Aset;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AsetController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm    = $request->get('q');
        $filterDept    = $request->get('department_id');
        $filterKantor  = $request->get('office_id');
        $filterKategori = $request->get('classification_id');

        $query = Aset::with(['department', 'classification', 'histories.receiver.office']);

        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('description', 'like', "%{$searchTerm}%")
                  ->orWhere('nomor_unik', 'like', "%{$searchTerm}%")
                  ->orWhere('serial_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('department', fn($dq) => $dq->where('nama_departemen', 'like', "%{$searchTerm}%"))
                  ->orWhereHas('histories.receiver', fn($rq) => $rq->where('name', 'like', "%{$searchTerm}%"));
            });
        }
        if ($filterDept)     $query->where('department_id', $filterDept);
        if ($filterKategori) $query->where('classification_id', $filterKategori);
        if ($filterKantor) {
            $query->whereHas('histories', function($q) use ($filterKantor) {
                $q->whereIn('created_at', function($sub) {
                    $sub->selectRaw('MAX(created_at)')->from('product_histories')->groupBy('product_id');
                })->whereHas('receiver', fn($r) => $r->where('office_id', $filterKantor));
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $perPage = (int) $request->get('per_page', 10);

        if (in_array($sortBy, ['created_at', 'description', 'nomor_unik', 'serial_number']) || \Illuminate\Support\Facades\Schema::hasColumn('products', $sortBy)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return response()->json($query->paginate($perPage));
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'data-inventaris-aset-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $products = Aset::with(['classification', 'department', 'histories.receiver'])->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.products', compact('products'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('data-inventaris-aset-' . date('Y-m-d') . '.pdf');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt']);

        $holderMappings = null;
        if ($request->has('holder_mappings') && !empty($request->input('holder_mappings'))) {
            $rawMappings = $request->input('holder_mappings');
            $holderMappings = is_string($rawMappings) ? json_decode($rawMappings, true) : $rawMappings;
        }

        // Jika belum ada mapping pemegang dan tidak dipaksa lewatkan
        if ($holderMappings === null && $request->input('force') != '1') {
            $checkResult = $this->checkHolders($request->file('file'));
            if (!empty($checkResult['holders'])) {
                return response()->json([
                    'status' => 'requires_holder_matching',
                    'total_rows' => $checkResult['total_rows'],
                    'holders_count' => count($checkResult['holders']),
                    'holders' => $checkResult['holders'],
                    'all_users' => $checkResult['all_users'],
                ]);
            }
        }

        Excel::import(new ProductsImport($holderMappings), $request->file('file'));
        return response()->json(['message' => 'Data aset berhasil diimport.']);
    }

    private function checkHolders($file)
    {
        $sheets = Excel::toArray(new \App\Imports\ProductsCheckImport, $file);
        $rows = $sheets[0] ?? [];

        $allUsers = User::select('id', 'name', 'employee_id', 'job_position', 'department_id')
            ->with('department:id,nama_departemen')
            ->get();

        $holderRows = [];
        $ignored = ['-', 'kosong', 'hilang', 'stock', 'stok', 'di kantor', 'dikantor', 'kantor', 'rusak', 'tersedia', 'null', 'none', ''];

        foreach ($rows as $index => $row) {
            $idBarang = trim((string)($row['id_barang'] ?? $row['nomor_unik'] ?? $row['id'] ?? ''));
            $categoryName = trim((string)($row['asset_category'] ?? $row['kategori_aset'] ?? $row['kategori'] ?? ''));
            $description = trim((string)($row['description'] ?? $row['nama_barang'] ?? $row['device_name'] ?? ''));
            $rawHolder = trim((string)($row['name'] ?? $row['full_name'] ?? $row['pemegang'] ?? ''));

            if (empty($idBarang) && empty($categoryName) && empty($description)) {
                continue;
            }

            $cleanHolder = strtolower($rawHolder);
            if (empty($cleanHolder) || in_array($cleanHolder, $ignored)) {
                continue;
            }

            // Cari kandidat yang mirip
            $candidates = [];
            $cleanWords = array_filter(
                explode(' ', preg_replace('/[^a-zA-Z0-9\s]/', ' ', $cleanHolder)),
                fn($w) => strlen($w) >= 2
            );

            foreach ($allUsers as $user) {
                $userName = strtolower($user->name);
                $score = 0;

                if ($userName === $cleanHolder) {
                    $score = 100;
                } elseif (str_contains($userName, $cleanHolder) || str_contains($cleanHolder, $userName)) {
                    $score = 90;
                } else {
                    $matchedWords = 0;
                    foreach ($cleanWords as $w) {
                        if (str_contains($userName, $w)) {
                            $matchedWords++;
                        }
                    }
                    if (count($cleanWords) > 0 && $matchedWords > 0) {
                        $score = max($score, 50 + ($matchedWords / count($cleanWords) * 40));
                    }

                    similar_text($cleanHolder, $userName, $pct);
                    $score = max($score, $pct);
                }

                if ($score >= 40) {
                    $candidates[] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'employee_id' => $user->employee_id ?: '-',
                        'job_position' => $user->job_position ?: '-',
                        'department' => $user->department?->nama_departemen ?: '-',
                        'score' => round($score, 1)
                    ];
                }
            }

            usort($candidates, fn($a, $b) => $b['score'] <=> $a['score']);
            $candidates = array_slice($candidates, 0, 8);

            $bestId = !empty($candidates) ? $candidates[0]['id'] : null;
            $isMatch = !empty($candidates) && $candidates[0]['score'] >= 75;

            $holderRows[] = [
                'row_index' => $index,
                'id_barang' => $idBarang ?: "Baris " . ($index + 2),
                'description' => $description ?: ($categoryName ?: 'Aset'),
                'category' => $categoryName,
                'holder_name' => $rawHolder,
                'candidates' => $candidates,
                'selected_user_id' => $bestId,
                'is_match' => $isMatch,
            ];
        }

        return [
            'total_rows' => count($rows),
            'holders' => $holderRows,
            'all_users' => $allUsers->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'employee_id' => $u->employee_id ?: '-',
                'job_position' => $u->job_position ?: '-',
            ]),
        ];
    }

    public function getNextNomor($departmentId)
    {
        $last = Aset::where('department_id', $departmentId)
            ->orderByRaw('CAST(nomor_unik AS UNSIGNED) DESC')
            ->value('nomor_unik');

        $nextNumber = $last ? ((int) $last) + 1 : 1;

        return response()->json([
            'next_nomor' => str_pad($nextNumber, 5, '0', STR_PAD_LEFT),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description'       => 'required|string|max:255',
            'classification_id' => 'required|exists:classifications,id',
            'nomor_unik'        => 'required|string',
            'department_id'     => 'nullable|exists:departments,id',
            'harga'             => 'nullable|string',
        ]);

        $deptId = $request->department_id ?: 1;
        $harga = $request->harga ? (int) str_replace('.', '', $request->harga) : null;

        $product = Aset::create([
            'description'       => $request->description,
            'classification_id' => $request->classification_id,
            'department_id'     => $deptId,
            'nomor_unik'        => strtoupper($request->nomor_unik),
            'harga'             => $harga,
            'url_token'         => Str::random(40),
            'serial_number'     => $request->serial_number,
            'processor'         => $request->processor,
            'ram'               => $request->ram,
            'new_ram'           => $request->new_ram,
            'ssd'               => $request->ssd,
            'new_ssd'           => $request->new_ssd,
            'os'                => $request->os,
            'screen_id'         => $request->screen_id,
            'imei'              => $request->imei,
            'mac_address'       => $request->mac_address,
            'device_class'      => $request->device_class,
            'device_name'       => $request->device_name,
            'remarks'           => $request->remarks,
            'has_headset'       => $request->has_headset ? true : false,
        ]);

        return response()->json(['message' => 'Aset berhasil ditambahkan.', 'data' => $product], 201);
    }

    public function show($id)
    {
        $product = Aset::with(['department', 'classification', 'histories.sender', 'histories.receiver.office'])->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Aset::findOrFail($id);
        
        $request->validate([
            'description'       => 'required|string|max:255',
            'classification_id' => 'required|exists:classifications,id',
            'nomor_unik'        => 'required|string',
            'department_id'     => 'nullable|exists:departments,id',
        ]);

        $deptId = $request->department_id ?: $product->department_id;
        $harga = $request->harga ? (int) str_replace('.', '', $request->harga) : null;

        $product->update([
            'description'       => $request->description,
            'classification_id' => $request->classification_id,
            'department_id'     => $deptId,
            'nomor_unik'        => strtoupper($request->nomor_unik),
            'harga'             => $harga,
            'serial_number'     => $request->serial_number,
            'processor'         => $request->processor,
            'ram'               => $request->ram,
            'new_ram'           => $request->new_ram,
            'ssd'               => $request->ssd,
            'new_ssd'           => $request->new_ssd,
            'os'                => $request->os,
            'screen_id'         => $request->screen_id,
            'imei'              => $request->imei,
            'mac_address'       => $request->mac_address,
            'device_class'      => $request->device_class,
            'device_name'       => $request->device_name,
            'remarks'           => $request->remarks,
            'has_headset'       => $request->has_headset ? true : false,
        ]);

        return response()->json(['message' => 'Aset berhasil diperbarui.', 'data' => $product]);
    }

    public function destroy(Request $request, $id)
    {
        $product = Aset::findOrFail($id);
        $product->update([
            'deletion_reason' => $request->deletion_reason,
            'selling_price'   => $request->selling_price ? (int) str_replace('.', '', $request->selling_price) : null,
        ]);
        $product->delete();
        return response()->json(['message' => 'Aset dipindahkan ke sampah.']);
    }

    public function trashIndex()
    {
        $trashedProducts = Aset::onlyTrashed()->with(['department', 'classification'])->get();
        return response()->json($trashedProducts);
    }

    public function restore($id)
    {
        Aset::onlyTrashed()->findOrFail($id)->restore();
        return response()->json(['message' => 'Aset dikembalikan.']);
    }

    public function forceDelete($id)
    {
        $product = Aset::onlyTrashed()->findOrFail($id);
        $product->histories()->delete();
        $product->forceDelete();
        return response()->json(['message' => 'Aset dihapus permanen.']);
    }

    public function printQr($id)
    {
        $product = Aset::with(['department', 'classification'])->findOrFail($id);
        $url = url('/scan/' . $product->url_token);
        
        // Return Base64 of SVG with errorCorrection 'M' and margin(1) optimized for 3cm x 3cm printing
        $qrCode = QrCode::format('svg')
            ->size(1000)
            ->errorCorrection('M')
            ->margin(1)
            ->generate($url);

        return response()->json([
            'url' => $url,
            'qr_svg' => base64_encode($qrCode),
            'full_nomor_unik' => $product->full_nomor_unik,
            'description' => $product->description,
            'device_name' => $product->device_name,
        ]);
    }

    public function historyIndex(Request $request)
    {
        $searchTerm     = $request->get('q');
        $filterDept     = $request->get('department_id');
        $filterKantor   = $request->get('office_id');
        $filterKategori = $request->get('classification_id');

        $query = ProductHistory::with(['product.department', 'product.classification', 'sender', 'receiver.office'])->latest();

        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('product', fn($pq) =>
                    $pq->where('description', 'like', "%{$searchTerm}%")
                       ->orWhere('nomor_unik', 'like', "%{$searchTerm}%")
                )
                ->orWhereHas('receiver', fn($rq) => $rq->where('name', 'like', "%{$searchTerm}%"))
                ->orWhereHas('sender',   fn($sq) => $sq->where('name', 'like', "%{$searchTerm}%"));
            });
        }
        if ($filterDept)     $query->whereHas('product', fn($q) => $q->where('department_id', $filterDept));
        if ($filterKategori) $query->whereHas('product', fn($q) => $q->where('classification_id', $filterKategori));
        if ($filterKantor)   $query->whereHas('receiver', fn($q) => $q->where('office_id', $filterKantor));

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $perPage = (int) $request->get('per_page', 10);

        if (in_array($sortBy, ['id', 'created_at', 'jenis_transaksi']) || \Illuminate\Support\Facades\Schema::hasColumn('product_histories', $sortBy)) {
            // Remove the default ->latest() order
            $query->getQuery()->orders = null; 
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->getQuery()->orders = null;
            $query->orderBy('created_at', 'desc');
        }

        return response()->json($query->paginate($perPage));
    }

    public function showHistory(ProductHistory $productHistory)
    {
        $productHistory->load(['sender', 'receiver', 'product']);
        return response()->json($productHistory);
    }

    public function updateHistory(Request $request, ProductHistory $productHistory)
    {
        $request->validate(['diterima_oleh' => 'required|exists:users,id']);
        $productHistory->update(['diterima_oleh' => $request->diterima_oleh]);
        return response()->json(['message' => 'Riwayat diupdate.', 'data' => $productHistory]);
    }

    public function cancelHistory(ProductHistory $productHistory)
    {
        $productHistory->delete();
        return response()->json(['message' => 'Riwayat dibatalkan.']);
    }

    public function quickReturn(ProductHistory $productHistory)
    {
        $latest = ProductHistory::where('product_id', $productHistory->product_id)->latest()->first();
        if ($latest->id !== $productHistory->id || $latest->jenis_transaksi !== 'meminjam') {
            return response()->json(['success' => false, 'message' => 'Aset ini sudah tidak aktif dipinjam atau sudah dikembalikan.'], 400);
        }

        $batchId = (string) \Illuminate\Support\Str::uuid();
        ProductHistory::create([
            'product_id'      => $productHistory->product_id,
            'dioper_oleh'     => auth()->id(),
            'diterima_oleh'   => null,
            'jenis_transaksi' => 'mengembalikan',
            'batch_id'        => $batchId,
            'pihak_pertama_id'=> auth()->id(),
            'tanggal_pinjam'  => now()->toDateString()
        ]);

        return response()->json(['success' => true, 'message' => 'Aset berhasil dikembalikan ke kantor.', 'batch_id' => $batchId]);
    }

    public function storeTransfer(Request $request)
    {
        $request->validate([
            'jenis_transaksi' => 'required|in:meminjam,kembali',
            'diterima_oleh'   => 'required_if:jenis_transaksi,meminjam|nullable|exists:users,id',
        ]);

        if ($request->jenis_transaksi === 'meminjam') {
            $request->validate([
                'product_ids'      => 'required|array|min:1',
                'product_ids.*'    => 'exists:products,id',
                'quantities'       => 'required|array',
                'quantities.*'     => 'integer|min:1',
            ]);

            $batchId = (string) \Illuminate\Support\Str::uuid();
            $tanggal = $request->tanggal_pinjam ?? now()->toDateString();
            $accessories = $request->has('accessories')
                ? (is_array($request->accessories) ? implode(',', array_filter($request->accessories)) : $request->accessories)
                : null;

            foreach ($request->product_ids as $index => $productId) {
                ProductHistory::create([
                    'product_id'       => $productId,
                    'dioper_oleh'      => auth()->id(),
                    'diterima_oleh'    => $request->diterima_oleh,
                    'jenis_transaksi'  => 'meminjam',
                    'batch_id'         => $batchId,
                    'quantity'         => $request->quantities[$index] ?? 1,
                    'pihak_pertama_id' => auth()->id(),
                    'tanggal_pinjam'   => $tanggal,
                    'catatan'          => $request->catatan,
                    'accessories'      => $accessories,
                ]);
            }

            return response()->json([
                'message' => 'Peminjaman berhasil! STTB siap.',
                'batch_id' => $batchId
            ]);
        }

        $request->validate(['product_id' => 'required|exists:products,id']);
        $lastHistory = ProductHistory::where('product_id', $request->product_id)->latest()->first();
        
        if (!$lastHistory || !$lastHistory->diterima_oleh) {
            return response()->json(['message' => 'Aset ini sudah berada di kantor.'], 400);
        }
        
        $batchId = (string) \Illuminate\Support\Str::uuid();
        $tanggal = $request->tanggal_pinjam ?? now()->toDateString();
        $accessories = $request->has('accessories')
            ? (is_array($request->accessories) ? implode(',', array_filter($request->accessories)) : $request->accessories)
            : null;
        
        ProductHistory::create([
            'product_id'      => $request->product_id,
            'dioper_oleh'     => $request->diterima_oleh ?? ($lastHistory->diterima_oleh ?? auth()->id()),
            'diterima_oleh'   => null,
            'jenis_transaksi' => 'mengembalikan',
            'catatan'         => $request->catatan,
            'accessories'     => $accessories,
            'tanggal_pinjam'  => $tanggal,
            'batch_id'        => $batchId,
            'pihak_pertama_id'=> auth()->id()
        ]);
        
        return response()->json(['message' => 'Aset berhasil dikembalikan ke kantor.', 'batch_id' => $batchId]);
    }

    public function showSttb($batchId)
    {
        $histories = ProductHistory::with(['product.department', 'receiver', 'pihakPertama'])
            ->where('batch_id', $batchId)
            ->get();

        if ($histories->isEmpty()) {
            return response()->json(['message' => 'STTB tidak ditemukan.'], 404);
        }

        $first        = $histories->first();
        $pihakPertama = $first->pihakPertama;
        $pihakKedua   = $first->receiver;
        $tanggal      = $first->tanggal_pinjam ?? $first->created_at->toDateString();

        return response()->json([
            'histories' => $histories,
            'pihak_pertama' => $pihakPertama,
            'pihak_kedua' => $pihakKedua,
            'tanggal' => $tanggal,
            'batch_id' => $batchId
        ]);
    }
}



