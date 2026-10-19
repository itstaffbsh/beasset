<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">
            {{ $type === 'meminjam' ? '🤝 Pinjam Aset — Buat STTB' : '🔄 Pengembalian Aset ke Kantor' }}
        </h2>
    </x-slot>

    {{-- Pre-proses data untuk JavaScript --}}
    @php
        $allUsers = $users->map(fn($u) => [
            'id'      => $u->id,
            'name'    => $u->name,
            'jabatan' => $u->job_position ?? ucfirst($u->role),
            'kantor'  => $u->office?->nama_kantor ?? '-',
        ])->values();

        $allAssets = $results->map(fn($p) => [
            'id'          => $p->id,
            'noUnik'      => $p->full_nomor_unik,
            'desc'        => $p->description,
            'dept'        => $p->department?->nama_departemen ?? '-',
            'klasifikasi' => $p->classification?->nama_klasifikasi ?? '',
        ])->values();
    @endphp
    <script>
        const ALL_USERS  = {!! json_encode($allUsers) !!};
        const ALL_ASSETS = {!! json_encode($allAssets) !!};
        const HOLDER_MAP = {!! json_encode($holderMap) !!};
        const TYPE       = "{{ $type }}";
    </script>

    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 py-10 space-y-6">

        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-4 rounded-xl border border-red-200">{{ session('error') }}</div>
        @endif

        {{-- ══ SECTION 1: DETAIL STTB (Hanya mode meminjam) ══ --}}
        @if($type === 'meminjam')
        <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] p-8">
            <h3 class="font-serif font-bold text-[#4a554a] text-lg mb-6">📋 Detail Serah Terima</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">

                {{-- PIHAK PERTAMA --}}
                <div>
                    <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">PIHAK PERTAMA — Yang Menyerahkan</label>
                    <div class="relative">
                        <input type="text" id="search_pihak1" autocomplete="off" placeholder="Ketik nama admin..."
                               class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring focus:ring-[#a47b53] focus:ring-opacity-20 text-sm"
                               oninput="filterUser(this, 'pihak1_results', 'pihak_pertama_hidden')">
                        <div id="pihak1_results" class="absolute z-50 w-full bg-white border border-[#e5e0d8] rounded-lg shadow-xl mt-1 hidden max-h-52 overflow-y-auto"></div>
                    </div>
                    <input type="hidden" id="pihak_pertama_hidden">
                    <p id="pihak1_selected" class="text-xs text-green-600 mt-1 hidden font-semibold"></p>
                </div>

                {{-- PIHAK KEDUA --}}
                <div>
                    <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">PIHAK KEDUA — Yang Menerima</label>
                    <div class="relative">
                        <input type="text" id="search_pihak2" autocomplete="off" placeholder="Ketik nama karyawan..."
                               class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring focus:ring-[#a47b53] focus:ring-opacity-20 text-sm"
                               oninput="filterUser(this, 'pihak2_results', 'pihak_kedua_hidden')">
                        <div id="pihak2_results" class="absolute z-50 w-full bg-white border border-[#e5e0d8] rounded-lg shadow-xl mt-1 hidden max-h-52 overflow-y-auto"></div>
                    </div>
                    <input type="hidden" id="pihak_kedua_hidden">
                    <p id="pihak2_selected" class="text-xs text-green-600 mt-1 hidden font-semibold"></p>
                </div>

                {{-- TANGGAL --}}
                <div>
                    <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Tanggal Peminjaman</label>
                    <input type="date" id="tanggal_pinjam_input" value="{{ date('Y-m-d') }}"
                           class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring focus:ring-[#a47b53] focus:ring-opacity-20 text-sm">
                </div>
            </div>
        </div>
        @endif

        {{-- ══ SECTION 2: DAFTAR ASET ══ --}}
        <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] overflow-hidden">
            <div class="px-8 py-5 border-b border-[#e5e0d8] bg-[#f9f8f6] flex items-center justify-between">
                <h3 class="font-serif font-bold text-[#4a554a] text-lg">
                    {{ $type === 'meminjam' ? '📦 Pilih Barang yang Akan Dipinjam' : '🔄 Pilih Barang yang Akan Dikembalikan' }}
                </h3>
                <span class="text-xs text-gray-400">{{ $results->count() }} aset tersedia</span>
            </div>

            {{-- Search Filter --}}
            <div class="px-8 py-4 border-b border-[#e5e0d8]">
                <input type="text" id="asset-search" placeholder="🔍 Filter: ketik nama, ID, atau departemen..."
                       class="w-full border-[#d1cdba] rounded-lg shadow-sm focus:border-[#a47b53] focus:ring focus:ring-[#a47b53] focus:ring-opacity-20 text-sm"
                       oninput="filterAssets(this.value)">
            </div>

            {{-- Asset List --}}
            <div id="asset-list" class="divide-y divide-[#f4f1ea] max-h-96 overflow-y-auto">
                @forelse($results as $product)
                    @php $lastH = $holderMap[$product->id] ?? null; @endphp
                    <div class="asset-row flex items-center justify-between px-8 py-4 hover:bg-[#f9f8f6] transition cursor-pointer group"
                         data-id="{{ $product->id }}"
                         data-no="{{ $product->full_nomor_unik }}"
                         data-desc="{{ $product->description }}"
                         data-dept="{{ $product->department?->nama_departemen }}"
                         onclick="{{ $type === 'meminjam' ? 'addToCart' : 'selectReturn' }}('{{ $product->id }}', '{{ $product->full_nomor_unik }}', '{{ addslashes($product->description) }}')">
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-lg
                                {{ Str::contains(strtolower($product->classification?->nama_klasifikasi ?? ''), 'laptop') ? 'bg-blue-100' : (Str::contains(strtolower($product->classification?->nama_klasifikasi ?? ''), 'hp') ? 'bg-purple-100' : 'bg-[#e8f5e4]') }}">
                                {{ Str::contains(strtolower($product->classification?->nama_klasifikasi ?? ''), 'laptop') ? '💻' : (Str::contains(strtolower($product->classification?->nama_klasifikasi ?? ''), 'hp') ? '📱' : '📦') }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-mono font-bold text-[#5c6b5b] text-sm">{{ $product->full_nomor_unik }}</p>
                                <p class="text-sm text-gray-700 truncate">{{ $product->description }}</p>
                                <p class="text-xs text-gray-400">{{ $product->department?->nama_departemen }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 flex-shrink-0 ml-4">
                            @if($lastH)
                                <div class="text-right hidden sm:block">
                                    <p class="text-xs font-semibold text-orange-600">📌 {{ $lastH['name'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $lastH['office'] }}</p>
                                </div>
                                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-bold">Dipinjam</span>
                            @else
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-bold">Tersedia</span>
                            @endif
                            <span class="text-xs font-bold text-[#a47b53] opacity-0 group-hover:opacity-100 transition uppercase tracking-widest">
                                {{ $type === 'meminjam' ? '+ Pilih →' : '↩ Kembalikan →' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-400 italic py-12">Tidak ada aset tersedia.</p>
                @endforelse
                <div id="no-result" class="hidden text-center text-gray-400 italic py-8 text-sm">Tidak ada aset yang cocok dengan pencarian.</div>
            </div>
        </div>

        {{-- ══ SECTION 3: KERANJANG (Hanya mode meminjam) ══ --}}
        @if($type === 'meminjam')
        <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] p-8">
            <h3 class="font-serif font-bold text-[#4a554a] text-lg mb-4">🛒 Daftar Barang yang Akan Dipinjam</h3>
            <div id="cart-empty" class="text-center text-gray-400 italic py-6 text-sm">
                Klik barang di atas untuk menambahkannya ke daftar.
            </div>
            <div id="cart-list" class="space-y-3 hidden"></div>
        </div>

        {{-- Form submit tersembunyi --}}
        <form id="sttb-form" action="{{ route('products.storeTransfer') }}" method="POST">
            @csrf
            <input type="hidden" name="jenis_transaksi" value="meminjam">
            <input type="hidden" name="pihak_pertama_id" id="form_pihak_pertama">
            <input type="hidden" name="diterima_oleh" id="form_pihak_kedua">
            <input type="hidden" name="tanggal_pinjam" id="form_tanggal">
            <div id="product-inputs"></div>
            <button type="button" onclick="submitSttb()"
                    class="w-full bg-[#a47b53] text-white py-4 rounded-xl shadow-lg hover:bg-[#8b6540] transition font-bold text-lg">
                📄 Proses Peminjaman & Buat STTB
            </button>
        </form>
        @endif

        {{-- ══ SECTION 3b: Form Pengembalian ══ --}}
        @if($type === 'kembali')
        <div id="return-confirm-box" class="hidden bg-[#5c6b5b] text-white rounded-xl p-8 shadow-lg">
            <p class="text-[#c8dfc6] text-sm uppercase tracking-widest mb-2">Konfirmasi Pengembalian</p>
            <h4 id="return-asset-name" class="text-xl font-serif font-bold mb-6"></h4>
            <form action="{{ route('products.storeTransfer') }}" method="POST">
                @csrf
                <input type="hidden" name="jenis_transaksi" value="kembali">
                <input type="hidden" name="product_id" id="return-product-id">
                <button type="submit" class="w-full bg-white text-[#5c6b5b] py-3 rounded-lg font-bold hover:bg-[#f4f1ea] transition">
                    ✅ Konfirmasi Pengembalian
                </button>
            </form>
        </div>
        @endif
    </div>

    <script>
        // ── KERANJANG (Mode Meminjam) ────────────────────────────────────────
        let cart = [];

        function addToCart(id, noUnik, desc) {
            if (cart.find(i => i.id === id)) {
                alert('Barang ' + noUnik + ' sudah ada di daftar!');
                return;
            }
            cart.push({ id, noUnik, desc, qty: 1 });
            renderCart();
            // Highlight baris yang sudah dipilih
            document.querySelectorAll('.asset-row').forEach(row => {
                if (row.dataset.id === id) row.classList.add('bg-green-50', 'border-l-4', 'border-green-500');
            });
        }

        function removeFromCart(id) {
            cart = cart.filter(i => i.id !== id);
            renderCart();
            document.querySelectorAll('.asset-row').forEach(row => {
                if (row.dataset.id === id) row.classList.remove('bg-green-50', 'border-l-4', 'border-green-500');
            });
        }

        function updateQty(id, val) {
            const item = cart.find(i => i.id === id);
            if (item) item.qty = parseInt(val) || 1;
        }

        function renderCart() {
            const listEl = document.getElementById('cart-list');
            const emptyEl = document.getElementById('cart-empty');
            if (!listEl) return;
            if (cart.length === 0) {
                listEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
                return;
            }
            listEl.classList.remove('hidden');
            emptyEl.classList.add('hidden');
            listEl.innerHTML = cart.map((item, i) => `
                <div class="flex items-center gap-4 p-4 border border-[#e5e0d8] rounded-xl bg-green-50">
                    <div class="w-7 h-7 bg-[#5c6b5b] text-white rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0">${i+1}</div>
                    <div class="flex-1 min-w-0">
                        <p class="font-mono font-bold text-[#5c6b5b] text-sm">${item.noUnik}</p>
                        <p class="text-xs text-gray-500 truncate">${item.desc}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <label class="text-xs text-gray-500">Jml:</label>
                        <input type="number" min="1" value="${item.qty}" onchange="updateQty('${item.id}', this.value)"
                               class="w-14 border-[#d1cdba] rounded text-center text-sm focus:border-[#a47b53]">
                    </div>
                    <button onclick="removeFromCart('${item.id}')" class="text-red-400 hover:text-red-600 text-lg flex-shrink-0">✕</button>
                </div>
            `).join('');
        }

        // ── MODE PENGEMBALIAN ────────────────────────────────────────────────
        function selectReturn(id, noUnik, desc) {
            document.getElementById('return-product-id').value = id;
            document.getElementById('return-asset-name').textContent = noUnik + ' — ' + desc;
            document.getElementById('return-confirm-box').classList.remove('hidden');
            document.getElementById('return-confirm-box').scrollIntoView({ behavior: 'smooth' });
        }

        // ── FILTER ASET (Live Search) ────────────────────────────────────────
        function filterAssets(q) {
            const rows = document.querySelectorAll('.asset-row');
            const noResult = document.getElementById('no-result');
            q = q.toLowerCase();
            let found = 0;
            rows.forEach(row => {
                const text = (row.dataset.no + ' ' + row.dataset.desc + ' ' + row.dataset.dept).toLowerCase();
                if (!q || text.includes(q)) {
                    row.classList.remove('hidden');
                    found++;
                } else {
                    row.classList.add('hidden');
                }
            });
            noResult.classList.toggle('hidden', found > 0);
        }

        // ── AUTOCOMPLETE USER ────────────────────────────────────────────────
        function filterUser(input, resultsId, hiddenId) {
            const q = input.value.toLowerCase();
            const resultsEl = document.getElementById(resultsId);
            const hiddenEl = document.getElementById(hiddenId);
            const selectedEl = document.getElementById(resultsId.replace('_results', '_selected'));

            // Reset hidden ID saat mengetik lagi
            hiddenEl.value = '';
            if (selectedEl) { selectedEl.classList.add('hidden'); selectedEl.textContent = ''; }

            if (!q || q.length < 1) { resultsEl.classList.add('hidden'); return; }

            const matches = ALL_USERS.filter(u => u.name.toLowerCase().includes(q)).slice(0, 8);

            if (matches.length === 0) { resultsEl.classList.add('hidden'); return; }

            resultsEl.innerHTML = matches.map(u => `
                <div class="px-4 py-3 hover:bg-[#f0f7ee] cursor-pointer transition border-b border-[#f4f1ea] last:border-0"
                     onclick="selectUser('${u.id}', '${u.name.replace(/'/g, "\\'")}', '${u.jabatan.replace(/'/g, "\\'")}', '${resultsId}', '${hiddenId}')">
                    <div class="font-semibold text-sm text-[#4a554a]">${u.name}</div>
                    <div class="text-xs text-gray-400">${u.jabatan} — ${u.kantor}</div>
                </div>
            `).join('');
            resultsEl.classList.remove('hidden');
        }

        function selectUser(id, name, jabatan, resultsId, hiddenId) {
            const num = resultsId.includes('pihak1') ? '1' : '2';
            document.getElementById('search_pihak' + num).value = name;
            document.getElementById(hiddenId).value = id;
            document.getElementById(resultsId).classList.add('hidden');

            const selectedEl = document.getElementById('pihak' + num + '_selected');
            if (selectedEl) {
                selectedEl.textContent = '✅ ' + name + ' — ' + jabatan;
                selectedEl.classList.remove('hidden');
            }
        }

        // Tutup dropdown autocomplete saat klik di luar
        document.addEventListener('click', function(e) {
            ['pihak1_results', 'pihak2_results'].forEach(id => {
                const el = document.getElementById(id);
                if (el && !el.contains(e.target) && e.target.id !== 'search_pihak1' && e.target.id !== 'search_pihak2') {
                    el.classList.add('hidden');
                }
            });
        });

        // ── SUBMIT STTB ──────────────────────────────────────────────────────
        function submitSttb() {
            const pihak1 = document.getElementById('pihak_pertama_hidden').value;
            const pihak2 = document.getElementById('pihak_kedua_hidden').value;
            const tanggal = document.getElementById('tanggal_pinjam_input').value;

            if (!pihak1) { alert('Harap pilih PIHAK PERTAMA (yang menyerahkan).'); return; }
            if (!pihak2) { alert('Harap pilih PIHAK KEDUA (penerima barang).'); return; }
            if (cart.length === 0) { alert('Harap tambahkan minimal 1 barang ke daftar.'); return; }

            document.getElementById('form_pihak_pertama').value = pihak1;
            document.getElementById('form_pihak_kedua').value = pihak2;
            document.getElementById('form_tanggal').value = tanggal;

            const inputsDiv = document.getElementById('product-inputs');
            inputsDiv.innerHTML = '';
            cart.forEach(item => {
                inputsDiv.innerHTML += `<input type="hidden" name="product_ids[]" value="${item.id}">`;
                inputsDiv.innerHTML += `<input type="hidden" name="quantities[]" value="${item.qty}">`;
            });

            document.getElementById('sttb-form').submit();
        }
    </script>
</x-app-layout>
