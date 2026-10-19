<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="font-serif text-2xl font-bold text-[#4a554a]">Selamat Datang</h2>
        <p class="text-gray-500 text-sm mt-1">Silakan masuk untuk mengelola aset</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-5">
            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full border-[#e5e0d8] rounded-xl shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] py-3" required autofocus placeholder="nama@email.com">
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs" />
        </div>

        <!-- Password -->
        <div class="mb-5">
            <label class="block text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-2">Password</label>
            <input type="password" name="password" class="w-full border-[#e5e0d8] rounded-xl shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] py-3" required placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs" />
        </div>

        <div class="flex items-center justify-between mb-8">
            <label class="inline-flex items-center">
                <input type="checkbox" name="remember" class="rounded border-[#e5e0d8] text-[#5c6b5b] focus:ring-[#5c6b5b]">
                <span class="ms-2 text-xs text-gray-500">Ingat saya</span>
            </label>
            
            @if (Route::has('password.request'))
                <a class="text-xs text-[#a47b53] hover:underline font-semibold" href="{{ route('password.request') }}">
                    Lupa Password?
                </a>
            @endif
        </div>

        <button type="submit" class="w-full bg-[#5c6b5b] text-white py-4 rounded-2xl font-bold shadow-lg hover:bg-[#4a554a] transition-all transform hover:-translate-y-1">
            Masuk ke Dashboard
        </button>
    </form>
</x-guest-layout>
