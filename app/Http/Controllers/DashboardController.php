<?php

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
        $totalAssets = Product::count();
        
        $latestHistories = DB::table('product_histories')
            ->select('product_id', DB::raw('MAX(id) as last_id'))
            ->groupBy('product_id');

        $borrowedAssets = DB::table('product_histories')
            ->joinSub($latestHistories, 'latest', function ($join) {
                $join->on('product_histories.id', '=', 'latest.last_id');
            })
            ->where('jenis_transaksi', 'meminjam')
            ->count();
            
        $availableAssets = $totalAssets - $borrowedAssets;
        
        $totalUsers = User::count();

        // Grafik: jumlah asset yang dipinjam perbulan selama setahun (tahun ini)
        $currentYear = Carbon::now()->year;
        
        $monthlyBorrowsRaw = ProductHistory::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(id) as total')
        )
        ->whereYear('created_at', $currentYear)
        ->where('jenis_transaksi', 'meminjam')
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->keyBy('month');
        
        $monthlyBorrows = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyBorrows[] = $monthlyBorrowsRaw->has($i) ? $monthlyBorrowsRaw[$i]->total : 0;
        }

        // Tabel Riwayat Peminjaman (10 item terbaru)
        $recentHistory = ProductHistory::with(['product', 'sender', 'receiver', 'pihakPertama'])
            ->where('jenis_transaksi', 'meminjam')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_assets' => $totalAssets,
                'available_assets' => $availableAssets,
                'borrowed_assets' => $borrowedAssets,
                'total_users' => $totalUsers,
            ],
            'chart' => [
                'year' => $currentYear,
                'data' => $monthlyBorrows
            ],
            'recent_history' => $recentHistory
        ]);
    }
}