<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informasi Pemilik - {{ $product->description }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f1ea; }
        h1, h2, h3 { font-family: 'Lora', serif; }
    </style>
</head>
<body class="antialiased pb-16">

    <!-- [BAGIAN: HEADER] | Menampilkan Identitas Utama Aset -->
    <div class="max-w-md mx-auto mt-8 px-4">
        <div class="bg-[#5c6b5b] text-white rounded-2xl px-6 py-5 mb-4 shadow-md">
            <p class="text-xs text-[#c8dfc6] mb-1 uppercase tracking-widest">Kepemilikan Aset</p>
            <h1 class="text-2xl font-bold leading-tight">{{ $product->description }}</h1>
            <p class="mt-2 font-mono text-sm bg-[#4a554a] inline-block px-2 py-0.5 rounded">{{ $product->full_nomor_unik }}</p>
        </div>

        <!-- [BAGIAN: PEMEGANG] | Menampilkan siapa yang membawa aset saat ini -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5e0d8] mb-4">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-3">Pemegang Saat Ini</p>
            @if($currentHistory)
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-[#e8f5e4] flex items-center justify-center text-[#3a5a3a] text-2xl font-bold">
                        {{ strtoupper(substr($currentHistory->receiver?->name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold text-[#4a554a] text-xl leading-tight">{{ $currentHistory->receiver?->name ?? 'Tidak diketahui' }}</p>
                        <p class="text-md text-gray-500">{{ $currentHistory->receiver?->office?->nama_kantor ?? 'Kantor Belum Terdaftar' }}</p>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Status Terakhir</p>
                    <span class="{{ $currentHistory->jenis_transaksi === 'peminjaman' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }} text-sm font-semibold px-3 py-1 rounded-full inline-block">
                        {{ ucfirst($currentHistory->jenis_transaksi) }}
                    </span>
                    <p class="text-xs text-gray-400 mt-2 italic">Sejak {{ $currentHistory->created_at->format('d M Y, H:i') }}</p>
                </div>
            @else
                <div class="py-8 text-center">
                    <p class="text-gray-400 italic">Belum ada riwayat kepemilikan.</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5e0d8]">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-3">Departemen Asal</p>
            <p class="text-lg font-bold text-[#4a554a]">{{ $product->department->nama_departemen }}</p>
            <p class="text-sm text-gray-500">Kode: {{ $product->department->kode_asset }}</p>
        </div>
    </div>

</body>
</html>
