<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4">
            <h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">Daftar Aset IT</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('products.export') }}" class="bg-[#a47b53] text-white px-4 py-2 rounded-md shadow hover:bg-[#8b6540] transition font-bold text-sm">EXPORT EXCEL</a>
                <button onclick="document.getElementById('import-modal-asset').classList.remove('hidden')" class="bg-[#d1cdba] text-[#4a554a] px-4 py-2 rounded-md shadow hover:bg-[#c4c0a8] transition font-bold text-sm">IMPORT EXCEL</button>
                <a href="{{ route('products.create') }}" class="bg-[#5c6b5b] text-[#f4f1ea] px-4 py-2 rounded-md shadow hover:bg-[#4a554a] transition font-bold text-sm">+ TAMBAH ASET</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Import Modal --}}
        <div id="import-modal-asset" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-8 rounded-xl shadow-xl w-96">
                <h3 class="text-lg font-bold mb-4">Import Data Aset</h3>
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" class="mb-4 w-full" required>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('import-modal-asset').classList.add('hidden')" class="px-4 py-2 text-gray-500">Batal</button>
                        <button type="submit" class="bg-[#5c6b5b] text-white px-4 py-2 rounded-md">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] overflow-hidden">
            <div class="p-8">
                @if(session('success'))
                    <div class="bg-[#d4ebd0] text-[#3a5a3a] p-4 rounded-md mb-6 border border-[#b8deb2]">{{ session('success') }}</div>
                @endif

                {{-- Search & Filter Bar --}}
                <div class="mb-6">
                    <form action="{{ route('products.index') }}" method="GET" id="filter-form-assets">
                        <div class="flex flex-wrap gap-3 items-end">
                            {{-- Search removed --}}
                            {{-- Filter Kantor --}}
                            <div class="min-w-[160px]">
                                <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-1">Kantor</label>
                                <select name="office_id" onchange="this.form.submit()" class="w-full border-[#d1cdba] rounded-lg py-2 text-sm shadow-sm focus:border-[#a47b53]">
                                    <option value="">Semua Kantor</option>
                                    @foreach($offices as $o)
                                        <option value="{{ $o->id }}" {{ $filterKantor == $o->id ? 'selected' : '' }}>{{ $o->nama_kantor }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Filter Kategori --}}
                            <div class="min-w-[160px]">
                                <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-1">Kategori</label>
                                <select name="classification_id" onchange="this.form.submit()" class="w-full border-[#d1cdba] rounded-lg py-2 text-sm shadow-sm focus:border-[#a47b53]">
                                    <option value="">Semua Kategori</option>
                                    @foreach($classifications as $c)
                                        <option value="{{ $c->id }}" {{ $filterKategori == $c->id ? 'selected' : '' }}>{{ $c->nama_klasifikasi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Tombol --}}
                            <div class="flex gap-2">
                                <button type="submit" class="bg-[#5c6b5b] text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-[#4a554a] transition">Cari</button>
                                @if($searchTerm || $filterDept || $filterKantor || $filterKategori)
                                    <a href="{{ route('products.index') }}" class="bg-red-50 text-red-500 border border-red-200 px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-100 transition">âœ• Reset</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1500px]">
                        <thead>
                            <tr class="bg-[#f9f8f6] border-b border-[#e5e0d8]">
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">Employee ID</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">Name</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">Category</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">Description</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">ID Barang</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">Processor</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">RAM</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">SSD</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">OS</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">S/N</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">MAC Address</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">IMEI</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">Screen ID</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">Harga</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider">Class</th>
                                <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-xs uppercase tracking-wider text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            @php 
                                $lastHistory = $product->histories->sortByDesc('created_at')->first(); 
                                $catColor = match(strtolower($product->classification->nama_klasifikasi ?? '')) {
                                    'laptop' => 'bg-green-100 text-green-700 border-green-200',
                                    'monitor' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'hp', 'smartphone' => 'bg-purple-100 text-purple-700 border-purple-200',
                                    'pc', 'computer' => 'bg-orange-100 text-orange-700 border-orange-200',
                                    default => 'bg-gray-100 text-gray-700 border-gray-200'
                                };
                            @endphp
                            <tr class="border-b border-[#f4f1ea] hover:bg-[#faf9f7] transition text-[11px]">
                                <td class="py-4 px-4 font-mono">{{ $lastHistory->receiver->employee_id ?? '-' }}</td>
                                <td class="py-4 px-4 font-medium text-gray-800">{{ $lastHistory->receiver->name ?? '-' }}</td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-0.5 rounded-full border {{ $catColor }} uppercase font-bold text-[9px]">
                                        {{ $product->classification->nama_klasifikasi }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-bold text-gray-700">{{ $product->description ?? '-' }}</td>
                                <td class="py-4 px-4 font-mono font-bold text-[#5c6b5b]">{{ $product->full_nomor_unik }}</td>
                                <td class="py-4 px-4 text-gray-500 max-w-[120px] truncate" title="{{ $product->processor }}">{{ $product->processor ?? '-' }}</td>
                                <td class="py-4 px-4 font-bold text-gray-700">{{ $product->ram ?? '-' }}</td>
                                <td class="py-4 px-4 text-gray-500">{{ $product->ssd ?? '-' }}</td>
                                <td class="py-4 px-4 text-gray-500">{{ $product->os ?? '-' }}</td>
                                <td class="py-4 px-4 font-mono text-gray-400">{{ $product->serial_number ?? '-' }}</td>
                                <td class="py-4 px-4 font-mono text-[10px] text-blue-600">{{ $product->mac_address ?? '-' }}</td>
                                <td class="py-4 px-4 font-mono text-[10px] text-purple-600">{{ $product->imei ?? '-' }}</td>
                                <td class="py-4 px-4 text-gray-500">{{ $product->screen_id ?? '-' }}</td>
                                <td class="py-4 px-4 font-bold text-[#a47b53]">Rp {{ number_format($product->harga, 0, ',', '.') }}</td>
                                <td class="py-4 px-4 text-center">
                                    <span class="px-2 py-1 rounded-md bg-[#f4f1ea] text-[#a47b53] border border-[#d1cdba] font-bold text-[9px]">
                                        {{ $product->device_class ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('products.show', $product->id) }}" class="p-1.5 bg-gray-50 rounded-lg hover:bg-gray-200 transition" title="Detail">ðŸ‘ï¸</a>
                                        <a href="{{ route('products.qr', $product->id) }}" class="p-1.5 bg-gray-50 rounded-lg hover:bg-gray-200 transition" title="QR">ðŸ“±</a>
                                        <a href="{{ route('products.edit', $product->id) }}" class="p-1.5 bg-gray-50 rounded-lg hover:bg-gray-200 transition" title="Edit">âœï¸</a>
                                        @if(auth()->user()->role === 'super_admin')
                                        <button type="button" onclick="openDeleteModal('{{ route('products.destroy', $product->id) }}', '{{ $product->description }}')" class="p-1.5 bg-red-50 rounded-lg hover:bg-red-100 transition" title="Hapus">ðŸ—‘ï¸</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @if($products->isEmpty())
                            <tr><td colspan="10" class="py-8 text-center text-gray-500 italic">Belum ada data aset.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Pagination handled by DataTables --}}
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closeDeleteModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div>
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Hapus Aset</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Anda akan menghapus aset <span id="deleteAssetName" class="font-bold"></span>. Silakan pilih alasan penghapusan:</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Alasan Penghapusan</label>
                            <select name="deletion_reason" id="deletion_reason" onchange="toggleSellingPrice()" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-[#5c6b5b] focus:border-[#5c6b5b] sm:text-sm" required>
                                <option value="dihancurkan">Dihancurkan (Scrapped)</option>
                                <option value="dijual">Dijual (Sold)</option>
                            </select>
                        </div>
                        <div id="sellingPriceContainer" class="hidden">
                            <label class="block text-sm font-medium text-gray-700">Harga Jual (Rp)</label>
                            <div class="flex mt-1 rounded-md shadow-sm">
                                <span class="inline-flex items-center px-3 text-gray-500 border border-r-0 border-gray-300 rounded-l-md bg-gray-50 sm:text-sm">Rp</span>
                                <input type="text" name="selling_price" id="selling_price" class="flex-1 block w-full border-gray-300 rounded-none rounded-r-md focus:ring-[#5c6b5b] focus:border-[#5c6b5b] sm:text-sm" placeholder="Contoh: 500.000">
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="submit" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm">Hapus (Backup)</button>
                        <button type="button" onclick="closeDeleteModal()" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5c6b5b] sm:mt-0 sm:col-start-1 sm:text-sm">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(actionUrl, assetName) {
            document.getElementById('deleteForm').action = actionUrl;
            document.getElementById('deleteAssetName').textContent = assetName;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function toggleSellingPrice() {
            const reason = document.getElementById('deletion_reason').value;
            const container = document.getElementById('sellingPriceContainer');
            const input = document.getElementById('selling_price');
            if (reason === 'dijual') {
                container.classList.remove('hidden');
                input.required = true;
            } else {
                container.classList.add('hidden');
                input.required = false;
                input.value = '';
                input.value = '';
            }
        }

        const sellingPriceInput = document.getElementById('selling_price');
        if (sellingPriceInput) {
            sellingPriceInput.addEventListener('keyup', function(e) {
                this.value = formatRupiah(this.value);
            });
        }

        function formatRupiah(angka) {
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if(ribuan){
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return rupiah;
        }
    </script>
</x-app-layout>

