<?php

namespace App\Http\Controllers;

use App\Models\Classification;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;

/* |--------------------------------------------------------------------------
   | [KONTROLLER MANAJEMEN ASET]
   |--------------------------------------------------------------------------
   | File ini adalah "Otak" yang mengatur semua alur logika aset, 
   | mulai dari menampilkan daftar, menambah, hingga proses pinjam/kembali.
   | 
   */

class ProductController extends Controller
{
    /* | [PROSEDUR] | 
       | Kegunaan: Menampilkan halaman utama daftar aset.
       | Menyiapkan data aset dan mengirimkannya ke file tampilan (Blade).
    */
    public function index()
    {
        /* | [ARRAY/KOLEKSI] | 
           | Mengambil semua data aset dari database beserta relasi departemennya.
        */
        $products = Product::with(['department', 'classification', 'histories.receiver'])->get();
        
        return view('products.index', compact('products'));
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'data_aset_it.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new ProductsImport, $request->file('file'));
        return back()->with('success', 'Data aset berhasil diimport.');
    }

    public function create()
    {
        $departments = Department::all();
        $classifications = Classification::all();
        return view('products.create', compact('departments', 'classifications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_asset'       => 'required|string|max:255',
            'classification_id'=> 'required|exists:classifications,id',
            'department_id'    => 'required|exists:departments,id',
            'nomor_unik'       => [
                'required', 'string', 'max:50',
                \Illuminate\Validation\Rule::unique('products')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                })
            ],
            'harga'            => 'nullable|string',
            'serial_number'    => 'nullable|string',
            'processor'        => 'nullable|string',
            'ram'              => 'nullable|string',
            'new_ram'          => 'nullable|string',
            'ssd'              => 'nullable|string',
            'new_ssd'          => 'nullable|string',
            'os'               => 'nullable|string',
            'screen_id'        => 'nullable|string',
            'imei'             => 'nullable|string',
            'mac_address'      => 'nullable|string',
            'device_class'     => 'nullable|string',
        ]);

        $harga = $request->harga ? (int) str_replace('.', '', $request->harga) : null;

        Product::create([
            'nama_asset'        => $request->nama_asset,
            'classification_id' => $request->classification_id,
            'department_id'     => $request->department_id,
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
        ]);

        return redirect()->route('products.index')->with('success', 'Aset berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['department', 'classification', 'histories.sender', 'histories.receiver.office']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $departments = Department::all();
        $classifications = Classification::all();
        return view('products.edit', compact('product', 'departments', 'classifications'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'nama_asset'       => 'required|string|max:255',
            'classification_id'=> 'required|exists:classifications,id',
            'department_id'    => 'required|exists:departments,id',
            'nomor_unik'       => [
                'required', 'string', 'max:50',
                \Illuminate\Validation\Rule::unique('products')->ignore($product->id)->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                })
            ],
            'harga'            => 'nullable|string',
            'serial_number'    => 'nullable|string',
            'processor'        => 'nullable|string',
            'ram'              => 'nullable|string',
            'new_ram'          => 'nullable|string',
            'ssd'              => 'nullable|string',
            'new_ssd'          => 'nullable|string',
            'os'               => 'nullable|string',
            'screen_id'        => 'nullable|string',
            'imei'             => 'nullable|string',
            'mac_address'      => 'nullable|string',
            'device_class'     => 'nullable|string',
        ]);

        $harga = $request->harga ? (int) str_replace('.', '', $request->harga) : null;

        $product->update([
            'nama_asset'        => $request->nama_asset,
            'classification_id' => $request->classification_id,
            'department_id'     => $request->department_id,
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
        ]);

        return redirect()->route('products.index')->with('success', 'Aset berhasil diupdate.');
    }

    public function destroy(Request $request, Product $product)
    {
        $hargaJual = $request->selling_price ? (int) str_replace('.', '', $request->selling_price) : null;
        
        $product->update([
            'deletion_reason' => $request->deletion_reason,
            'selling_price'   => $hargaJual,
        ]);
        
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Aset dipindahkan ke tempat sampah (Backup Data).');
    }

    public function trashIndex()
    {
        $trashedProducts = Product::onlyTrashed()->with(['department', 'classification'])->get();
        return view('products.trash', compact('trashedProducts'));
    }

    public function restore($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        return redirect()->route('products.trash')->with('success', 'Aset berhasil dikembalikan (Restore).');
    }

    public function forceDelete($id)
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->histories()->delete(); 
        $product->forceDelete();
        return redirect()->route('products.trash')->with('success', 'Aset berhasil dihapus permanen.');
    }

    public function printQr(Product $product)
    {
        $url = route('scan.show', $product->url_token);
        $qrCode = QrCode::size(300)->generate($url);
        return view('products.qr', compact('product', 'qrCode', 'url'));
    }

    public function pinjam(Request $request)
    {
        return $this->handleTransfer($request, 'meminjam');
    }

    public function kembali(Request $request)
    {
        return $this->handleTransfer($request, 'kembali');
    }

    private function handleTransfer(Request $request, $type)
    {
        $query = $request->get('q', '');
        $results = collect();
        if ($query) {
            $results = Product::with(['department', 'classification', 'histories.receiver.office'])
                ->whereHas('department', function ($q) use ($query) {
                    $q->whereRaw("CONCAT(kode_asset, '-', nomor_unik) LIKE ?", ["%{$query}%"]);
                })
                ->orWhere('nama_asset', 'like', "%{$query}%")
                ->orWhere('nomor_unik', 'like', "%{$query}%")
                ->get();

            // Filter: Jika transaksi 'kembali', hanya tampilkan barang yang sedang dipinjam (ada pemilik)
            if ($type === 'kembali') {
                $results = $results->filter(function($p) {
                    $last = $p->histories->sortByDesc('created_at')->first();
                    return $last && $last->diterima_oleh;
                });
            }
        }
        $users = User::where('role', 'user')->with('office')->get();

        $holderMap = [];
        foreach ($results as $p) {
            $last = $p->histories->sortByDesc('created_at')->first();
            $holderMap[$p->id] = ($last && $last->diterima_oleh) ? [
                'name'   => $last->receiver?->name ?? '-',
                'office' => $last->receiver?->office?->nama_kantor ?? '-',
                'date'   => $last->created_at->format('d M Y H:i'),
            ] : null;
        }

        return view('products.transfer', compact('query', 'results', 'users', 'holderMap', 'type'));
    }

    public function storeTransfer(Request $request)
    {
        $request->validate([
            'product_id'      => 'required|exists:products,id',
            'diterima_oleh'   => 'required_if:jenis_transaksi,meminjam|nullable|exists:users,id',
            'jenis_transaksi' => 'required|in:meminjam,kembali',
        ]);

        if ($request->jenis_transaksi === 'kembali') {
            $lastHistory = ProductHistory::where('product_id', $request->product_id)->latest()->first();
            if (!$lastHistory || !$lastHistory->diterima_oleh) {
                return back()->with('error', 'Aset ini sudah berada di kantor.');
            }
        }

        ProductHistory::create([
            'product_id'      => $request->product_id,
            'dioper_oleh'     => auth()->id(),
            'diterima_oleh'   => $request->jenis_transaksi === 'kembali' ? null : $request->diterima_oleh,
            'jenis_transaksi' => $request->jenis_transaksi === 'kembali' ? 'mengembalikan' : $request->jenis_transaksi,
        ]);

        $msg = $request->jenis_transaksi === 'meminjam' ? 'Aset berhasil dipinjamkan.' : 'Aset berhasil dikembalikan ke kantor.';
        return redirect()->route('products.' . ($request->jenis_transaksi === 'meminjam' ? 'pinjam' : 'kembali'))->with('success', $msg);
    }

    public function historyIndex(Request $request)
    {
        $query = ProductHistory::with(['product.department', 'sender', 'receiver.office'])->latest();
        
        if ($request->has('q') && $request->q != '') {
            $searchTerm = $request->q;
            $query->whereHas('product', function($q) use ($searchTerm) {
                $q->where('nama_asset', 'like', "%{$searchTerm}%")
                  ->orWhere('nomor_unik', 'like', "%{$searchTerm}%");
            })->orWhereHas('sender', function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            })->orWhereHas('receiver', function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%");
            });
        }

        $histories = $query->get();
        return view('products.history_index', compact('histories'));
    }

    public function editHistory(ProductHistory $productHistory)
    {
        $users = User::where('role', 'user')->with('office')->get();
        return view('products.edit_history', compact('productHistory', 'users'));
    }

    public function updateHistory(Request $request, ProductHistory $productHistory)
    {
        $request->validate([
            'diterima_oleh'   => 'required|exists:users,id',
        ]);

        $productHistory->update([
            'diterima_oleh'   => $request->diterima_oleh,
        ]);

        return redirect()->route('products.show', $productHistory->product_id)->with('success', 'Riwayat berhasil diupdate.');
    }

    public function cancelHistory(ProductHistory $productHistory)
    {
        $productHistory->delete();
        return back()->with('success', 'Riwayat berhasil dibatalkan.');
    }
}
