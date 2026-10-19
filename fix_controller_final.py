import sys

content = r'''<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        \ = Product::count();
        
        \ = DB::table('product_histories')
            ->select('product_id', DB::raw('MAX(id) as last_id'))
            ->groupBy('product_id');

        \ = DB::table('product_histories')
            ->joinSub(\, 'latest', function (\) {
                \->on('product_histories.id', '=', 'latest.last_id');
            })
            ->where('jenis_transaksi', 'meminjam')
            ->count();
            
        \ = \ - \;
        
        \ = User::count();

        // Grafik: jumlah asset yang dipinjam perbulan selama setahun (tahun ini)
        \ = Carbon::now()->year;
        
        \ = ProductHistory::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(id) as total')
        )
        ->whereYear('created_at', \)
        ->where('jenis_transaksi', 'meminjam')
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->keyBy('month');
        
        \ = [];
        for (\ = 1; \ <= 12; \++) {
            \[] = \->has(\) ? \[\]->total : 0;
        }

        // Tabel Riwayat Peminjaman (10 item terbaru)
        \ = ProductHistory::with(['product', 'sender', 'receiver', 'pihakPertama'])
            ->where('jenis_transaksi', 'meminjam')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => [
                'total_assets' => \,
                'available_assets' => \,
                'borrowed_assets' => \,
                'total_users' => \,
            ],
            'chart' => [
                'year' => \,
                'data' => \
            ],
            'recent_history' => \
        ]);
    }
}
'''
content = content.replace('\$', '$')

with open('app/Http/Controllers/DashboardController.php', 'w', encoding='utf-8') as f:
    f.write(content)

print("DashboardController rewritten clean")
