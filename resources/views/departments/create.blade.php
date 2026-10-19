<x-app-layout>
    <x-slot name="header"><h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">Tambah Departemen</h2></x-slot>
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] p-8">
            @if ($errors->any())
                <div class="bg-red-100 text-red-800 p-4 rounded-md mb-6"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('departments.store') }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label class="block text-[#5c6b5b] font-semibold mb-2">Nama Departemen</label>
                    <input type="text" name="nama_departemen" value="{{ old('nama_departemen') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm" required>
                </div>
                <div class="mb-6">
                    <label class="block text-[#5c6b5b] font-semibold mb-2">Kode Departemen <span class="text-sm font-normal text-gray-400">(contoh: HR, AC, IT)</span></label>
                    <input type="text" name="kode_asset" value="{{ old('kode_asset') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm uppercase" maxlength="10" required>
                </div>
                <div class="flex items-center gap-4">
                    <button type="submit" class="bg-[#5c6b5b] text-[#f4f1ea] px-6 py-2 rounded-md shadow hover:bg-[#4a554a] transition">Simpan</button>
                    <a href="{{ route('departments.index') }}" class="text-[#8b6540] hover:underline">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
