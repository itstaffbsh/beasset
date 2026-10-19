<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">
            {{ __('Edit Klasifikasi') }}
        </h2>
    </x-slot>

    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] overflow-hidden">
            <div class="p-8">
                
                @if ($errors->any())
                    <div class="bg-red-100 text-red-800 p-4 rounded-md mb-6 shadow-sm">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('classifications.update', $classification->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-5">
                        <label class="block text-[#5c6b5b] font-semibold mb-2">Nama Klasifikasi</label>
                        <input type="text" name="nama_klasifikasi" value="{{ old('nama_klasifikasi', $classification->nama_klasifikasi) }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring focus:ring-[#a47b53] focus:ring-opacity-50" required>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="bg-[#5c6b5b] text-[#f4f1ea] px-6 py-2 rounded-md shadow hover:bg-[#4a554a] transition duration-200">Update</button>
                        <a href="{{ route('classifications.index') }}" class="text-[#8b6540] hover:underline">Batal</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
