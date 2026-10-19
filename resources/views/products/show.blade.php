<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">Detail Aset: {{ $product->description }}</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('products.qr', $product->id) }}" class="bg-[#5c6b5b] text-white px-4 py-2 rounded-md shadow hover:bg-[#4a554a] transition">📱 Cetak QR</a>
                <a href="{{ route('products.index') }}" class="text-[#8b6540] hover:underline flex items-center">← Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Kolom Kiri: Info Utama & Spesifikasi --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Card Spesifikasi Teknis --}}
                <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#e5e0d8] bg-[#f9f8f6] flex justify-between items-center">
                        <h3 class="font-serif font-bold text-[#4a554a] flex items-center gap-2">
                            <span>💻</span> Spesifikasi Teknis
                        </h3>
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold border bg-green-100 text-green-700 border-green-200 uppercase">
                            {{ $product->classification->nama_klasifikasi }}
                        </span>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-6 gap-x-12">
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">ID Barang / Nomor Unik</p>
                                <p class="text-lg font-mono font-bold text-[#5c6b5b]">{{ $product->full_nomor_unik }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Serial Number (S/N)</p>
                                <p class="text-lg font-mono text-gray-700">{{ $product->serial_number ?? '-' }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Processor</p>
                                <p class="text-lg text-[#4a554a] font-semibold">{{ $product->processor ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">RAM (Total / New)</p>
                                <p class="text-lg text-[#4a554a] font-semibold">{{ $product->ram ?? '-' }} <span class="text-sm text-green-600 font-normal">({{ $product->new_ram ?? 'No Upgrade' }})</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">SSD (Total / New)</p>
                                <p class="text-lg text-[#4a554a] font-semibold">{{ $product->ssd ?? '-' }} <span class="text-sm text-green-600 font-normal">({{ $product->new_ssd ?? 'No Upgrade' }})</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Operating System</p>
                                <p class="text-base text-gray-600">{{ $product->os ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Device Class</p>
                                <span class="px-2 py-1 rounded-md text-sm font-bold bg-[#f4f1ea] text-[#a47b53] border border-[#d1cdba]">{{ $product->device_class ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Konektivitas & Identitas --}}
                <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#e5e0d8] bg-[#f9f8f6]">
                        <h3 class="font-serif font-bold text-[#4a554a] flex items-center gap-2">
                            <span>📡</span> Konektivitas & Identitas
                        </h3>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">MAC Address</p>
                                <p class="font-mono text-sm">{{ $product->mac_address ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">IMEI</p>
                                <p class="font-mono text-sm">{{ $product->imei ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Screen ID</p>
                                <p class="text-sm">{{ $product->screen_id ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-widest mb-1">Harga Perolehan</p>
                                <p class="text-sm font-bold text-[#a47b53]">Rp {{ number_format($product->harga, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Status & Pemegang --}}
            <div class="space-y-6">
                @php $currentHistory = $product->histories->sortByDesc('created_at')->first(); @endphp
                
                {{-- Card Pemegang --}}
                <div class="bg-[#5c6b5b] text-white rounded-xl shadow-lg p-8 relative overflow-hidden">
                    <div class="relative z-10">
                        <p class="text-xs text-[#c8dfc6] uppercase tracking-[0.2em] mb-4">Pemegang Saat Ini</p>
                        @if($currentHistory && $currentHistory->diterima_oleh)
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-3xl font-bold">
                                    {{ substr($currentHistory->receiver->name, 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="text-xl font-serif font-bold">{{ $currentHistory->receiver->name }}</h4>
                                    <p class="text-sm text-[#c8dfc6]">{{ $currentHistory->receiver->employee_id }}</p>
                                </div>
                            </div>
                            <div class="space-y-2 text-sm text-[#e8f5e4]">
                                <div class="flex justify-between border-b border-white/10 pb-2">
                                    <span>Kantor:</span>
                                    <span class="font-bold">{{ $currentHistory->receiver->office->nama_kantor ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between border-b border-white/10 pb-2">
                                    <span>Dipinjam Sejak:</span>
                                    <span class="font-bold">{{ $currentHistory->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                        @else
                            <div class="py-8 text-center">
                                <div class="text-5xl mb-4 opacity-50">🏢</div>
                                <h4 class="text-xl font-serif font-bold">Tersedia di Kantor</h4>
                                <p class="text-sm text-[#c8dfc6] mt-2 italic">Belum ada pemegang aktif</p>
                            </div>
                        @endif
                        
                        <div class="mt-8">
                            <a href="{{ route('products.' . ($currentHistory && $currentHistory->diterima_oleh ? 'kembali' : 'pinjam'), ['q' => $product->full_nomor_unik]) }}" 
                               class="block w-full text-center bg-white text-[#5c6b5b] py-3 rounded-lg font-bold hover:bg-[#f4f1ea] transition shadow-md">
                                {{ $currentHistory && $currentHistory->diterima_oleh ? '🔄 Proses Pengembalian' : '🤝 Proses Peminjaman' }}
                            </a>
                        </div>
                    </div>
                    {{-- Dekorasi background --}}
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full"></div>
                </div>

                {{-- Card Departemen --}}
                <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] p-6 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-widest mb-2">Departemen Pemilik</p>
                    <h4 class="text-2xl font-serif font-bold text-[#4a554a]">{{ $product->department->nama_departemen }}</h4>
                    <p class="text-sm text-gray-500 mt-1">Kode Asset: <span class="font-mono font-bold">{{ $product->department->kode_asset }}</span></p>
                </div>
            </div>
        </div>

        {{-- Tabel Riwayat Mutasi --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] overflow-hidden">
            <div class="px-6 py-4 border-b border-[#e5e0d8] bg-[#f9f8f6]">
                <h3 class="font-serif font-bold text-[#4a554a] flex items-center gap-2">
                    <span>📜</span> Riwayat Mutasi Aset
                </h3>
            </div>
            <div class="p-0">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="py-3 px-6 text-xs font-bold text-gray-400 uppercase">Tanggal</th>
                            <th class="py-3 px-6 text-xs font-bold text-gray-400 uppercase">Aksi</th>
                            <th class="py-3 px-6 text-xs font-bold text-gray-400 uppercase">Penanggung Jawab</th>
                            <th class="py-3 px-6 text-xs font-bold text-gray-400 uppercase">Penerima / Lokasi</th>
                            <th class="py-3 px-6 text-xs font-bold text-gray-400 uppercase">Status</th>
                            <th class="py-3 px-6 text-xs font-bold text-gray-400 uppercase text-center">STTB</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($product->histories->sortByDesc('created_at') as $history)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="py-4 px-6 text-sm text-gray-600">{{ $history->created_at->format('d/m/Y') }}</td>
                                <td class="py-4 px-6">
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase {{ $history->jenis_transaksi === 'meminjam' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $history->jenis_transaksi }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-sm font-medium">{{ $history->sender->name ?? '-' }}</td>
                                <td class="py-4 px-6 text-sm">{{ $history->receiver->name ?? '-' }}</td>
                                <td class="py-4 px-6">
                                    @if($history->diterima_oleh)
                                        <span class="text-xs text-blue-600 italic">Peminjaman Aktif</span>
                                    @else
                                        <span class="text-xs text-green-600 italic">Tersedia di Kantor</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if(!empty($history->batch_id))
                                        <a href="{{ route('sttb.show', $history->batch_id) }}" target="_blank"
                                           class="inline-flex items-center gap-1 bg-[#f4f1ea] text-[#a47b53] hover:bg-[#a47b53] hover:text-white text-xs font-bold px-3 py-1.5 rounded-lg transition border border-[#d1cdba]">
                                            📄 STTB
                                        </a>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
