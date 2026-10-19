<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">Master Riwayat Aset</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] overflow-hidden">
            <div class="px-8 py-5 border-b border-[#e5e0d8] bg-[#f9f8f6]">
                <h4 class="font-serif text-lg font-bold text-[#4a554a] mb-4">Semua Catatan Riwayat Mutasi</h4>
                <form method="GET" action="{{ route('history.index') }}">
                    <div class="flex flex-wrap gap-3 items-end">
                        {{-- Search removed --}}
                        {{-- Filter Kantor --}}
                        <div class="min-w-[150px]">
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-1">Kantor</label>
                            <select name="office_id" onchange="this.form.submit()" class="w-full border-[#d1cdba] rounded-lg py-2 text-sm shadow-sm focus:border-[#a47b53]">
                                <option value="">Semua Kantor</option>
                                @foreach($offices as $o)
                                    <option value="{{ $o->id }}" {{ ($filterKantor ?? '') == $o->id ? 'selected' : '' }}>{{ $o->nama_kantor }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Filter Kategori --}}
                        <div class="min-w-[150px]">
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-1">Kategori Aset</label>
                            <select name="classification_id" onchange="this.form.submit()" class="w-full border-[#d1cdba] rounded-lg py-2 text-sm shadow-sm focus:border-[#a47b53]">
                                <option value="">Semua Kategori</option>
                                @foreach($classifications as $c)
                                    <option value="{{ $c->id }}" {{ ($filterKategori ?? '') == $c->id ? 'selected' : '' }}>{{ $c->nama_klasifikasi }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Tombol --}}
                        <div class="flex gap-2">
                            <button type="submit" class="bg-[#5c6b5b] text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-[#4a554a] transition">Cari</button>
                            @if(($searchTerm ?? '') || ($filterDept ?? '') || ($filterKantor ?? '') || ($filterKategori ?? ''))
                                <a href="{{ route('history.index') }}" class="bg-red-50 text-red-500 border border-red-200 px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-100 transition">âœ• Reset</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="p-0">
                @if(session('success'))
                    <div class="m-8 bg-[#d4ebd0] text-[#3a5a3a] p-4 rounded-md border border-[#b8deb2]">{{ session('success') }}</div>
                @endif

                @if($histories->isEmpty())
                    <p class="text-center text-gray-400 italic py-12">Tidak ada data riwayat transfer ditemukan.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-white border-b border-[#e5e0d8]">
                                <th class="py-4 px-6 font-semibold text-[#5c6b5b]">Waktu</th>
                                <th class="py-4 px-6 font-semibold text-[#5c6b5b]">Informasi Aset</th>
                                <th class="py-4 px-6 font-semibold text-[#5c6b5b]">Dari (Pengirim)</th>
                                <th class="py-4 px-6 font-semibold text-[#5c6b5b]">Ke (Penerima)</th>
                                <th class="py-4 px-6 font-semibold text-[#5c6b5b] text-center">STTB</th>
                                @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                                <th class="py-4 px-6 font-semibold text-[#5c6b5b] text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($histories as $history)
                            <tr class="border-b border-[#f4f1ea] hover:bg-[#faf9f7] transition">
                                <td class="py-4 px-6 text-gray-500 whitespace-nowrap">{{ $history->created_at->format('d M Y') }}</td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-[#4a554a]">{{ $history->product->description ?? 'Aset Terhapus' }}</div>
                                    <div class="text-xs text-gray-500 mt-1 font-mono">{{ $history->product->full_nomor_unik ?? '-' }}</div>
                                </td>
                                <td class="py-4 px-6">{{ $history->sender?->name ?? 'Sistem' }}</td>
                                <td class="py-4 px-6">
                                    <div class="font-medium text-[#4a554a]">{{ $history->receiver?->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ $history->receiver?->office?->nama_kantor ?? '-' }}</div>
                                </td>
                                
                                <td class="py-4 px-6 text-center">
                                    @if(!empty($history->batch_id))
                                        <a href="{{ route('sttb.show', $history->batch_id) }}" target="_blank"
                                           class="inline-flex items-center gap-1 bg-[#f4f1ea] text-[#a47b53] hover:bg-[#a47b53] hover:text-white text-xs font-bold px-3 py-1.5 rounded-lg transition border border-[#d1cdba] whitespace-nowrap">
                                            ðŸ“„ Lihat STTB
                                        </a>
                                    @else
                                        <span class="text-gray-300 text-xs">â€”</span>
                                    @endif
                                </td>
                                
                                @if(in_array(auth()->user()->role, ['admin', 'super_admin']))
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center space-x-3">
                                        <a href="{{ route('history.edit', $history->id) }}" class="text-blue-500 hover:text-blue-700 text-xs font-medium bg-blue-50 px-2 py-1 rounded">Edit</a>
                                        @if(auth()->user()->role === 'super_admin')
                                        <form action="{{ route('history.cancel', $history->id) }}" method="POST" onsubmit="return confirm('Batalkan riwayat ini? Status aset akan dikembalikan ke posisi sebelumnya.');" class="inline m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium bg-red-50 px-2 py-1 rounded">Batalkan</button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination Links handled by DataTables --}}
                
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

