<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4">
            <h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">
                {{ __('Daftar Departemen') }}
            </h2>
            <a href="{{ route('departments.create') }}" class="bg-[#5c6b5b] text-[#f4f1ea] px-4 py-2 rounded-md shadow hover:bg-[#4a554a] transition duration-200">Tambah Departemen</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] overflow-hidden">
            <div class="p-8">
                @if(session('success'))
                    <div class="bg-[#d4ebd0] text-[#3a5a3a] p-4 rounded-md mb-6 shadow-sm border border-[#b8deb2]">{{ session('success') }}</div>
                @endif
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#f9f8f6] border-b border-[#e5e0d8]">
                            <th class="py-3 px-4 font-semibold text-[#5c6b5b]">Nama Departemen</th>
                            <th class="py-3 px-4 font-semibold text-[#5c6b5b]">Kode Departemen</th>
                            <th class="py-3 px-4 font-semibold text-[#5c6b5b] text-center w-48">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $dept)
                        <tr class="border-b border-[#f4f1ea] hover:bg-[#faf9f7] transition duration-150">
                            <td class="py-3 px-4">{{ $dept->nama_departemen }}</td>
                            <td class="py-3 px-4"><span class="bg-[#e8f5e4] text-[#3a5a3a] font-mono px-2 py-1 rounded">{{ $dept->kode_asset }}</span></td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('departments.edit', $dept->id) }}" class="text-[#a47b53] hover:text-[#8b6540] mr-3 font-medium">Edit</a>
                                <form action="{{ route('departments.destroy', $dept->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus departemen ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                        @if($departments->isEmpty())
                        <tr><td colspan="3" class="py-8 text-center text-gray-500 italic">Belum ada data departemen.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
