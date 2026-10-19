<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-red-800 leading-tight">🗑️ Backup Data (Aset Terhapus)</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden">
            <div class="px-8 py-5 border-b border-red-200 bg-red-50">
                <p class="text-sm text-red-600">Aset di bawah ini adalah data yang telah dihapus (Soft Deletes). Anda dapat mengembalikannya (Restore) atau menghapusnya secara permanen (Force Delete).</p>
            </div>
            <div class="p-8">
                @if(session('success'))
                    <div class="bg-green-100 text-green-800 p-4 rounded-md mb-6 border border-green-200">{{ session('success') }}</div>
                @endif
                
                @if($trashedProducts->isEmpty())
                    <p class="text-center text-gray-400 italic py-6">Tidak ada data aset terhapus saat ini.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-[#f9f8f6] border-b border-[#e5e0d8]">
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b]">Nomor Unik</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b]">Nama Aset</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b]">Alasan</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b]">Info Penjualan</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b]">Waktu Dihapus</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-center">Aksi (Khusus Super Admin)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trashedProducts as $product)
                            <tr class="border-b border-[#f4f1ea] hover:bg-red-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-red-600">{{ $product->full_nomor_unik }}</td>
                                <td class="py-3 px-4 font-medium">{{ $product->description }}</td>
                                <td class="py-3 px-4 text-sm">
                                    @if($product->deletion_reason === 'dijual')
                                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold uppercase">Dijual</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-bold uppercase">Dihancurkan</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    @if($product->deletion_reason === 'dijual')
                                        <span class="text-green-700 font-bold">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-gray-500">{{ $product->deleted_at->format('d M Y H:i') }}</td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-3">
                                        <form action="{{ route('products.restore', $product->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('Kembalikan aset ini ke daftar aktif?');">
                                            @csrf
                                            <button type="submit" class="bg-green-100 text-green-700 hover:bg-green-200 text-xs font-semibold px-3 py-1.5 rounded transition">♻️ Restore</button>
                                        </form>

                                        <form action="{{ route('products.forceDelete', $product->id) }}" method="POST" class="inline m-0" onsubmit="return confirm('PERINGATAN: Aset dan seluruh riwayat mutasinya akan dihapus secara permanen. Lanjutkan?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="bg-red-100 text-red-700 hover:bg-red-200 text-xs font-semibold px-3 py-1.5 rounded transition">🗑️ Hapus Permanen</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
