<x-app-layout>
    <x-slot name="header"><h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">Tambah Aset Baru</h2></x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 py-8">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Kolom Kiri: Informasi Dasar --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] p-6">
                        <h3 class="font-serif font-bold text-[#4a554a] mb-4 flex items-center gap-2">
                            <span>📦</span> Informasi Dasar
                        </h3>
                        
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Klasifikasi Barang</label>
                            <select name="classification_id" id="classification_id" class="w-full" required>
                                <option value="">-- Pilih Klasifikasi --</option>
                                @foreach($classifications as $c)
                                    <option value="{{ $c->id }}" {{ old('classification_id') == $c->id ? 'selected' : '' }}>{{ $c->nama_klasifikasi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Description (Model/Nama Barang)</label>
                            <input type="text" name="description" value="{{ old('description') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="Contoh: Laptop Lenovo ThinkPad" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Departemen Pemilik</label>
                            <select name="department_id" id="department_id" class="w-full" required>
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}" data-kode="{{ $d->kode_asset }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>
                                        {{ $d->nama_departemen }} ({{ $d->kode_asset }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Nomor Unik Barang</label>
                            <div class="flex items-center">
                                <span id="kode-prefix" class="px-3 py-2 bg-[#f4f1ea] text-[#a47b53] font-mono font-bold rounded-l-md border border-r-0 border-[#d1cdba]">DEPT-</span>
                                <input type="text" name="nomor_unik" id="nomor_unik" value="{{ old('nomor_unik') }}" class="flex-1 border-[#d1cdba] rounded-r-md shadow-sm focus:border-[#a47b53]" placeholder="Pilih departemen untuk auto-fill..." required>
                            </div>
                            <p id="nomor-status" class="text-xs mt-1 text-gray-400 italic">← Pilih departemen untuk mengisi otomatis</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Harga Perolehan (Rp)</label>
                            <input type="text" name="harga" id="harga" value="{{ old('harga') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="Contoh: 15.000.000">
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] p-6">
                        <h3 class="font-serif font-bold text-[#4a554a] mb-4 flex items-center gap-2">
                            <span>🆔</span> Identitas Perangkat
                        </h3>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Serial Number (S/N)</label>
                            <input type="text" name="serial_number" value="{{ old('serial_number') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="Masukkan S/N">
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">IMEI (Untuk Smartphone)</label>
                            <input type="text" name="imei" value="{{ old('imei') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="Masukkan IMEI">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">MAC Address</label>
                            <input type="text" name="mac_address" value="{{ old('mac_address') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="00:00:00:00:00:00">
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Spesifikasi Teknis --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] p-6">
                        <h3 class="font-serif font-bold text-[#4a554a] mb-4 flex items-center gap-2">
                            <span>💻</span> Spesifikasi Teknis
                        </h3>
                        
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Processor</label>
                            <input type="text" name="processor" value="{{ old('processor') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="Contoh: Intel Core i7-12700H">
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">RAM Bawaan</label>
                                <input type="text" name="ram" value="{{ old('ram') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="8GB">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">RAM Baru (Upgrade)</label>
                                <input type="text" name="new_ram" value="{{ old('new_ram') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="16GB">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">SSD Bawaan</label>
                                <input type="text" name="ssd" value="{{ old('ssd') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="256GB">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">SSD Baru (Upgrade)</label>
                                <input type="text" name="new_ssd" value="{{ old('new_ssd') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="512GB">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Operating System (OS)</label>
                            <input type="text" name="os" value="{{ old('os') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="Windows 11 Pro">
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Screen ID</label>
                            <input type="text" name="screen_id" value="{{ old('screen_id') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]" placeholder="Masukkan Screen ID">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Device Class</label>
                            <select name="device_class" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53]">
                                <option value="">-- Pilih Class --</option>
                                @foreach(\App\Models\Aset::DEVICE_CLASSES as $class)
                                    <option value="{{ $class }}" {{ old('device_class') == $class ? 'selected' : '' }}>{{ $class }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <button type="submit" class="flex-1 bg-[#5c6b5b] text-white py-3 rounded-xl shadow-lg hover:bg-[#4a554a] transition font-bold text-lg">
                            💾 Simpan Aset Baru
                        </button>
                        <a href="{{ route('products.index') }}" class="px-6 py-3 border border-[#d1cdba] rounded-xl text-gray-500 hover:bg-gray-50 transition font-bold text-lg text-center">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Scripts & Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#classification_id, #department_id').select2({ width: '100%' });

            $('#department_id').on('change', function() {
                const kode = $(this).find(':selected').data('kode');
                const deptId = $(this).val();

                // Update prefix label
                $('#kode-prefix').text(kode ? kode + '-' : 'DEPT-');

                // Auto-fetch nomor berikutnya dari server
                if (deptId) {
                    $('#nomor_unik').prop('disabled', true).val('Memuat...');
                    $('#nomor-status').text('🔄 Mengambil nomor...').removeClass('text-green-600 text-red-500').addClass('text-gray-400');

                    const url = "{{ route('products.nextNomor', ':id') }}".replace(':id', deptId);
                    $.getJSON(url, function(data) {
                        $('#nomor_unik').prop('disabled', false).val(data.next_nomor);
                        $('#nomor-status').text('✅ Nomor otomatis — bisa diubah jika perlu').removeClass('text-gray-400 text-red-500').addClass('text-green-600');
                    }).fail(function() {
                        $('#nomor_unik').prop('disabled', false).val('');
                        $('#nomor-status').text('⚠️ Gagal mengambil nomor, isi manual').removeClass('text-gray-400 text-green-600').addClass('text-red-500');
                    });
                } else {
                    $('#nomor_unik').val('');
                    $('#nomor-status').text('');
                }
            }).trigger('change');

            const hargaInput = document.getElementById('harga');
            if (hargaInput) {
                hargaInput.addEventListener('keyup', function(e) {
                    let val = this.value.replace(/[^,\d]/g, '').toString();
                    let split = val.split(',');
                    let sisa = split[0].length % 3;
                    let rupiah = split[0].substr(0, sisa);
                    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                    if (ribuan) {
                        let separator = sisa ? '.' : '';
                        rupiah += separator + ribuan.join('.');
                    }
                    this.value = rupiah;
                });
            }
        });
    </script>
    <style>
        .select2-container--default .select2-selection--single { height: 42px; border-color: #d1cdba; border-radius: 6px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
    </style>
</x-app-layout>
