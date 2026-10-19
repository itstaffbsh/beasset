<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">
            {{ __('Profil Saya') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Success Flash --}}
            @if(session('status') === 'profile-updated')
                <div class="bg-[#d4ebd0] text-[#3a5a3a] p-4 rounded-xl border border-[#b8deb2] flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Profil berhasil diperbarui!
                </div>
            @endif

            {{-- Profile Form --}}
            <div class="p-6 sm:p-8 bg-white shadow-sm border border-[#e5e0d8] rounded-xl">
                @include('profile.partials.update-profile-information-form')
            </div>

            {{-- Password Form --}}
            <div class="p-6 sm:p-8 bg-white shadow-sm border border-[#e5e0d8] rounded-xl">
                @include('profile.partials.update-password-form')
            </div>

            {{-- Delete Account Form --}}
            <div class="p-6 sm:p-8 bg-white shadow-sm border border-[#e5e0d8] rounded-xl">
                @include('profile.partials.delete-user-form')
            </div>

        </div>
    </div>
</x-app-layout>
