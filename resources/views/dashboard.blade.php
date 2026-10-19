<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">Dashboard</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Welcome banner --}}
        <div class="bg-gradient-to-r from-[#5c6b5b] to-[#4a554a] text-white rounded-xl p-8 mb-6 shadow-md">
            <h3 class="text-2xl font-serif font-bold mb-1">Selamat Datang, {{ Auth::user()->name }}!</h3>
            <p class="text-[#c8dfc6] text-sm">
                @if(auth()->user()->role === 'super_admin') Super Administrator
                @elseif(auth()->user()->role === 'admin') Administrator
                @else Pengguna — {{ auth()->user()->office?->nama_kantor ?? '-' }}
                @endif
            </p>
        </div>

        {{-- Stats --}}
        @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-[#e5e0d8] p-6 shadow-sm">
                <p class="text-sm text-gray-400 mb-1">Total Aset</p>
                <p class="text-3xl font-bold text-[#4a554a]">{{ \App\Models\Aset::count() }}</p>
            </div>
            <div class="bg-white rounded-xl border border-[#e5e0d8] p-6 shadow-sm">
                <p class="text-sm text-gray-400 mb-1">Total Departemen</p>
                <p class="text-3xl font-bold text-[#4a554a]">{{ \App\Models\Department::count() }}</p>
            </div>
            <div class="bg-white rounded-xl border border-[#e5e0d8] p-6 shadow-sm">
                <p class="text-sm text-gray-400 mb-1">Total Transaksi</p>
                <p class="text-3xl font-bold text-[#4a554a]">{{ \App\Models\ProductHistory::count() }}</p>
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl border border-[#e5e0d8] p-8 shadow-sm">
            <h4 class="font-serif text-lg font-bold text-[#4a554a] mb-4">Menu Cepat</h4>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                <a href="{{ route('products.index') }}" class="flex flex-col items-center p-4 bg-[#f4f1ea] rounded-lg hover:bg-[#e8e4da] transition text-center border border-[#e5e0d8]">
                    <span class="text-3xl mb-2">📦</span>
                    <span class="text-sm font-semibold text-[#4a554a]">Daftar Aset</span>
                </a>
                <a href="{{ route('products.meminjam') }}" class="flex flex-col items-center p-4 bg-[#e8f5e4] rounded-lg hover:bg-[#d4ebd0] transition text-center border border-[#c8dfc6]">
                    <span class="text-3xl mb-2">🤝</span>
                    <span class="text-sm font-semibold text-[#3a5a3a]">Pinjam Aset</span>
                </a>
                <a href="{{ route('products.kembali') }}" class="flex flex-col items-center p-4 bg-[#f0f9ff] rounded-lg hover:bg-[#e0f2fe] transition text-center border border-[#bae6fd]">
                    <span class="text-3xl mb-2">🔄</span>
                    <span class="text-sm font-semibold text-[#0369a1]">Kembali Aset</span>
                </a>
                @endif
                @if(auth()->user()->role === 'super_admin')
                <a href="{{ route('users.index') }}" class="flex flex-col items-center p-4 bg-[#f4f1ea] rounded-lg hover:bg-[#e8e4da] transition text-center border border-[#e5e0d8]">
                    <span class="text-3xl mb-2">👥</span>
                    <span class="text-sm font-semibold text-[#4a554a]">Kelola Users</span>
                </a>
                <a href="{{ route('offices.index') }}" class="flex flex-col items-center p-4 bg-[#f4f1ea] rounded-lg hover:bg-[#e8e4da] transition text-center border border-[#e5e0d8]">
                    <span class="text-3xl mb-2">🏢</span>
                    <span class="text-sm font-semibold text-[#4a554a]">Data Kantor</span>
                </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
