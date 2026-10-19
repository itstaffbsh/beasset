<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informasi Profil') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Perbarui foto, nama, email, nomor telepon, dan alamat akun Anda.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- ===== AVATAR ===== --}}
        <div>
            <x-input-label value="Foto Profil" />
            <div class="mt-2 flex items-center gap-5">
                {{-- Preview --}}
                <div class="relative group cursor-pointer" onclick="document.getElementById('avatar_input').click()">
                    <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-[#d1cdba] bg-[#f4f1ea] flex items-center justify-center">
                        @if($user->avatar)
                            <img id="avatar_preview"
                                 src="{{ Storage::url($user->avatar) }}"
                                 alt="Foto Profil"
                                 class="w-full h-full object-cover">
                        @else
                            <img id="avatar_preview"
                                 src=""
                                 alt=""
                                 class="w-full h-full object-cover hidden">
                            <svg id="avatar_placeholder" class="w-10 h-10 text-[#9e9e9e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        @endif
                    </div>
                    {{-- Overlay edit icon --}}
                    <div class="absolute inset-0 rounded-full bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <button type="button" onclick="document.getElementById('avatar_input').click()"
                            class="text-sm font-medium text-[#5c6b5b] hover:text-[#4a554a] underline">
                        Ganti Foto
                    </button>
                    <p class="text-xs text-gray-400 mt-0.5">JPG, PNG, WebP — maks. 2MB</p>
                </div>
            </div>
            <input type="file" id="avatar_input" name="avatar" accept="image/*" class="hidden"
                   onchange="previewAvatar(event)">
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        {{-- ===== NAME ===== --}}
        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                          :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- ===== EMAIL ===== --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                          :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- ===== PHONE ===== --}}
        <div>
            <x-input-label for="phone" value="Nomor Telepon" />
            <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full"
                          :value="old('phone', $user->phone)" placeholder="Contoh: 08123456789" autocomplete="tel" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        {{-- ===== ADDRESS ===== --}}
        <div>
            <x-input-label for="address" value="Alamat" />
            <textarea id="address" name="address" rows="3"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                      placeholder="Tulis alamat lengkap...">{{ old('address', $user->address) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        {{-- ===== SAVE BUTTON ===== --}}
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Tersimpan!') }}</p>
            @endif
        </div>
    </form>
</section>

<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('avatar_preview');
        const placeholder = document.getElementById('avatar_placeholder');
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
    };
    reader.readAsDataURL(file);
}
</script>
