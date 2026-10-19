<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">Edit Riwayat Transfer</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Tom Select CSS -->
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <style>
            .ts-control { border-radius: 0.375rem !important; padding: 0.625rem !important; border-color: #d1cdba !important; }
            .ts-dropdown { border-radius: 0.375rem !important; }
        </style>

        <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] p-8">
            @if ($errors->any())
                <div class="bg-red-100 text-red-800 p-4 rounded-md mb-6">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('history.update', $productHistory->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-1">Aset</label>
                        <div class="font-bold text-[#4a554a]">{{ $productHistory->product->description }}</div>
                        <div class="text-xs text-gray-500 font-mono">{{ $productHistory->product->full_nomor_unik }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-1">Tipe Transaksi</label>
                        <span class="inline-block px-2 py-1 rounded text-xs font-bold {{ $productHistory->jenis_transaksi == 'meminjam' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                            {{ ucfirst($productHistory->jenis_transaksi) }}
                        </span>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-[#5c6b5b] font-semibold mb-2">Penerima (Diterima Oleh)</label>
                    <select id="select-receiver" name="diterima_oleh" placeholder="Ketik nama untuk mencari..." autocomplete="off">
                        <option value="">Cari nama penerima...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ (old('diterima_oleh', $productHistory->diterima_oleh) == $user->id) ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->office?->nama_kantor ?? 'No Office' }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1 italic">* Anda dapat mengetik langsung nama penerima di kotak di atas.</p>
                </div>

                <div class="flex items-center gap-4 mt-8">
                    <button type="submit" class="bg-[#5c6b5b] text-[#f4f1ea] px-6 py-2.5 rounded-md shadow hover:bg-[#4a554a] transition font-semibold">Simpan Perubahan</button>
                    <a href="{{ route('products.show', $productHistory->product_id) }}" class="text-[#8b6540] hover:text-[#705030] font-medium hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tom Select JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        new TomSelect("#select-receiver",{
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    </script>
</x-app-layout>
