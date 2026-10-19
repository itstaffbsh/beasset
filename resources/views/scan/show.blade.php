<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informasi Aset - {{ $product->description }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f1ea; }
        h1, h2, h3 { font-family: 'Lora', serif; }
    </style>
</head>
<body class="antialiased pb-16">

    <div class="max-w-md mx-auto mt-8 px-4">

        {{-- [BAGIAN ATAS] | Header Card: Menampilkan Nama Aset, Klasifikasi, dan Departemen --}}
        <div class="bg-[#5c6b5b] text-white rounded-2xl px-6 py-5 mb-4 shadow-md">
            <p class="text-xs text-[#c8dfc6] mb-1 uppercase tracking-widest">Informasi Aset</p>
            <h1 class="text-2xl font-bold leading-tight">{{ $product->description }}</h1>
            <p class="text-sm text-[#c8dfc6] mt-1">{{ $product->classification->nama_klasifikasi }} &bull; {{ $product->department->nama_departemen }} ({{ $product->department->kode_asset }})</p>
            <p class="mt-2 font-mono text-sm bg-[#4a554a] inline-block px-2 py-0.5 rounded">{{ $product->full_nomor_unik }}</p>
        </div>

        @if(session('success'))
            <div class="bg-[#d4ebd0] text-[#3a5a3a] p-4 rounded-xl mb-4 border border-[#b8deb2] text-sm">
                ✅ {{ session('success') }}
            </div>
        @endif

        {{-- [BAGIAN SPEK] | Spesifikasi Teknis Aset --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5e0d8] mb-4">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-4">Spesifikasi Teknis</p>
            <div class="grid grid-cols-2 gap-y-4 gap-x-2">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">Processor</p>
                    <p class="text-sm font-semibold text-[#4a554a]">{{ $product->processor ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">RAM / Upgrade</p>
                    <p class="text-sm font-semibold text-[#4a554a]">{{ $product->ram }} / {{ $product->new_ram ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">Penyimpanan / Upgrade</p>
                    <p class="text-sm font-semibold text-[#4a554a]">{{ $product->ssd }} / {{ $product->new_ssd ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">OS</p>
                    <p class="text-sm font-semibold text-[#4a554a]">{{ $product->os ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">S/N</p>
                    <p class="text-sm font-mono text-[#4a554a]">{{ $product->serial_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase">Screen ID</p>
                    <p class="text-sm font-semibold text-[#4a554a]">{{ $product->screen_id ?? '-' }}</p>
                </div>
                <div class="col-span-2 pt-2 border-t border-gray-50">
                    <p class="text-[10px] text-gray-400 uppercase">MAC / IMEI</p>
                    <p class="text-xs font-mono text-[#4a554a]">{{ $product->mac_address ?? '-' }} / {{ $product->imei ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- [BAGIAN TENGAH] | Pemegang Saat Ini: Menampilkan siapa yang membawa barang ini sekarang --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5e0d8] mb-4">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-3">Pemegang Saat Ini</p>
            @if($currentHistory)
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#e8f5e4] flex items-center justify-center text-[#3a5a3a] text-xl font-bold">
                        {{ strtoupper(substr($currentHistory->receiver?->name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-[#4a554a] text-lg leading-tight">{{ $currentHistory->receiver?->name ?? 'Tidak diketahui' }}</p>
                        <p class="text-sm text-gray-400">{{ $currentHistory->receiver?->office?->nama_kantor ?? '-' }}</p>
                        <span class="{{ $currentHistory->jenis_transaksi === 'peminjaman' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }} text-xs font-semibold px-2 py-0.5 rounded-full mt-1 inline-block">
                            {{ ucfirst($currentHistory->jenis_transaksi) }}
                        </span>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-3">Sejak {{ $currentHistory->created_at->format('d M Y, H:i') }}</p>
            @else
                <div class="flex items-center gap-3 text-gray-400">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-2xl">📦</div>
                    <div>
                        <p class="font-semibold text-gray-500">Belum ada pemegang</p>
                        <p class="text-xs">Aset ini belum pernah dipinjamkan atau dikirim.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- [BAGIAN BAWAH] | Riwayat: Menampilkan daftar orang-orang yang pernah memegang barang ini --}}
        @if($product->histories->count() > 1)
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5e0d8] mb-4">
            <p class="text-xs text-gray-400 uppercase tracking-widest mb-3">Riwayat Penggunaan</p>
            <div class="space-y-3">
                @foreach($product->histories->sortByDesc('created_at') as $h)
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-8 h-8 rounded-full bg-[#f4f1ea] flex items-center justify-center text-xs font-bold text-[#5c6b5b] flex-shrink-0">
                        {{ strtoupper(substr($h->receiver?->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-[#4a4a4a]">{{ $h->receiver?->name ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $h->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <span class="{{ $h->jenis_transaksi === 'peminjaman' ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600' }} text-xs px-2 py-0.5 rounded-full">
                        {{ ucfirst($h->jenis_transaksi) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Login CTA --}}
        @guest
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e5e0d8] text-center">
            <p class="text-sm text-gray-500 mb-3">Ingin melakukan peminjaman atau pengiriman aset ini?</p>
            <a href="{{ route('login') }}" class="inline-block bg-[#5c6b5b] text-white px-6 py-2 rounded-lg hover:bg-[#4a554a] transition text-sm font-semibold">Login untuk Update</a>
        </div>
        @endguest

    </div>

</body>
</html>
