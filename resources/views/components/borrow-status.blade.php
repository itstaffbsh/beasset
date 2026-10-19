<div class="bg-white rounded-t-3xl md:rounded-xl shadow-sm overflow-hidden max-w-sm md:max-w-none font-sans mx-auto flex flex-col md:flex-row w-full">
    
    <!-- Item 1: Peminjaman Belum Sukses -->
    <div class="relative flex items-center md:justify-center gap-4 px-6 py-4 cursor-pointer hover:bg-gray-50 transition md:flex-1 md:border-r border-gray-100">
        <svg class="w-6 h-6 text-orange-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-semibold text-slate-500 text-[16px] max-[339px]:text-[15px] md:text-center">Peminjaman Belum Sukses</span>
    </div>

    <!-- Item 2: Peminjaman Sukses -->
    <div class="relative flex items-center md:justify-center gap-4 px-6 py-4 cursor-pointer hover:bg-gray-50 transition md:flex-1 md:border-r border-gray-100">
        <svg class="w-6 h-6 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-semibold text-slate-500 text-[16px] max-[339px]:text-[15px] md:text-center">Peminjaman Sukses</span>
    </div>

    <!-- Item 3: Pengembalian Belum Sukses -->
    <div class="relative flex items-center md:justify-center gap-4 px-6 py-4 cursor-pointer hover:bg-gray-50 transition md:flex-1 md:border-r border-gray-100">
        <svg class="w-6 h-6 text-orange-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-semibold text-slate-500 text-center flex-1 md:flex-none text-[16px] max-[339px]:text-[15px] leading-tight md:text-center">Pengembalian Belum<br>Sukses</span>
    </div>

    <!-- Item 4: Pengembalian Sukses (Active) -->
    <div class="relative flex items-center md:justify-center gap-4 px-6 py-4 cursor-pointer bg-[#f4f6f9] transition md:flex-1">
        <!-- Active Border -->
        <div class="absolute left-0 top-0 bottom-0 w-1.5 md:w-full md:h-1.5 md:bottom-0 md:top-auto md:left-0 bg-[#1e293b]"></div>
        
        <svg class="w-6 h-6 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-bold text-[#1e293b] text-[16px] max-[339px]:text-[15px] md:text-center">Pengembalian Sukses</span>
    </div>

</div>
