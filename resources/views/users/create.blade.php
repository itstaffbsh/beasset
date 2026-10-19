<x-app-layout>
    <x-slot name="header"><h2 class="font-serif text-2xl font-bold text-[#4a554a] leading-tight">Tambah Akun Karyawan</h2></x-slot>
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 py-10">
        <div class="bg-white rounded-xl shadow-sm border border-[#e5e0d8] p-8">
            @if ($errors->any())
                <div class="bg-red-50 text-red-800 p-4 rounded-lg mb-6 border border-red-200">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-[#5c6b5b] text-xs font-bold uppercase tracking-widest mb-2">Employee ID (Kosongkan untuk otomatis)</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] focus:ring-opacity-20" placeholder="Contoh: 00001">
                    </div>
                    <div>
                        <label class="block text-[#5c6b5b] text-xs font-bold uppercase tracking-widest mb-2">Peran (Role)</label>
                        <select name="role" id="role-select" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] focus:ring-opacity-20" required onchange="togglePassword()">
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Pengguna (User)</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                            @if(auth()->user()->role === 'super_admin')
                            <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-[#5c6b5b] text-xs font-bold uppercase tracking-widest mb-2">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] focus:ring-opacity-20" required>
                    </div>
                    <div>
                        <label class="block text-[#5c6b5b] text-xs font-bold uppercase tracking-widest mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] focus:ring-opacity-20" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-[#5c6b5b] text-xs font-bold uppercase tracking-widest mb-2">Jabatan (Job Position)</label>
                        <input type="text" name="job_position" value="{{ old('job_position') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] focus:ring-opacity-20" placeholder="Contoh: Marketing Staff">
                    </div>
                    <div>
                        <label class="block text-[#5c6b5b] text-xs font-bold uppercase tracking-widest mb-2">Level Pekerjaan</label>
                        <input type="text" name="job_level" value="{{ old('job_level') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] focus:ring-opacity-20" placeholder="Contoh: Junior / Senior">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-[#5c6b5b] text-xs font-bold uppercase tracking-widest mb-2">Tanggal Bergabung</label>
                        <input type="date" name="join_date" value="{{ old('join_date') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] focus:ring-opacity-20">
                    </div>
                    <div>
                        <label class="block text-[#5c6b5b] text-xs font-bold uppercase tracking-widest mb-2">Nomor Telepon</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] focus:ring-opacity-20" placeholder="081234567890">
                    </div>
                </div>

                <div id="password-container" class="{{ in_array(old('role'), ['admin', 'super_admin']) ? '' : 'hidden' }} mb-5">
                    <label class="block text-[#5c6b5b] text-xs font-bold uppercase tracking-widest mb-2">Password Login</label>
                    <input type="password" name="password" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] focus:ring-opacity-20" placeholder="Minimal 8 karakter">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                    <div>
                        <label class="block text-[#5c6b5b] text-xs font-bold uppercase tracking-widest mb-2">Kantor</label>
                        <select name="office_id" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] focus:ring-opacity-20" required>
                            <option value="">-- Pilih Kantor --</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}" {{ old('office_id') == $office->id ? 'selected' : '' }}>{{ $office->nama_kantor }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[#5c6b5b] text-xs font-bold uppercase tracking-widest mb-2">Departemen</label>
                        <select name="department_id" class="w-full border-[#d1cdba] rounded-md shadow-sm focus:border-[#a47b53] focus:ring-[#a47b53] focus:ring-opacity-20">
                            <option value="">-- Pilih Departemen --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->nama_departemen }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="bg-[#5c6b5b] text-white px-8 py-3 rounded-lg shadow-lg hover:bg-[#4a554a] transition font-bold uppercase tracking-wider text-sm">Simpan Akun</button>
                    <a href="{{ route('users.index') }}" class="text-[#8b6540] hover:underline font-semibold text-sm">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const role = document.getElementById('role-select').value;
            const container = document.getElementById('password-container');
            if (role === 'admin' || role === 'super_admin') {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>
