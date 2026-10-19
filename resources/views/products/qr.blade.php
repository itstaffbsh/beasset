<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR - {{ $product->description }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f4f1ea; }
        h1, h2, h3 { font-family: 'Lora', serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; margin: 0; padding: 0; }
            .qr-card { box-shadow: none !important; border: 1px solid #eee !important; margin: 0 !important; }
        }
    </style>
</head>
<body class="p-8">

    <div class="max-w-3xl mx-auto">
        <div class="flex justify-between items-center mb-8 no-print">
            <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-gray-700 font-bold">&larr; Kembali</a>
            <button onclick="window.print()" class="bg-[#a47b53] text-white px-6 py-2 rounded-xl font-bold shadow-lg hover:bg-[#8b6540] transition">
                🖨️ Cetak QR
            </button>
        </div>

        <div class="bg-white rounded-3xl p-8 shadow-xl border border-[#e5e0d8] text-center qr-card">
            <div class="mb-8 border-b border-gray-100 pb-6">
                <h3 class="font-bold text-3xl text-[#4a554a] mb-2">{{ $product->description }}</h3>
                <p class="font-mono text-[#a47b53] text-lg font-bold">{{ $product->full_nomor_unik }}</p>
                <p class="text-gray-400 text-sm mt-1">{{ $product->department->nama_departemen }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                {{-- QR SPEK --}}
                <div class="p-6 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">A. QR Spesifikasi Teknis</p>
                    <div class="flex justify-center mb-4">
                        <div class="p-3 bg-white rounded-xl shadow-sm">
                            {!! QrCode::size(160)->generate(route('scan.specs', $product->url_token)) !!}
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400 italic">Scan untuk melihat Processor, RAM, SSD, dll</p>
                </div>

                {{-- QR PEMILIK --}}
                <div class="p-6 bg-[#f8faf7] rounded-2xl border-2 border-dashed border-[#d4ebd0]">
                    <p class="text-xs font-bold text-[#5c6b5b] uppercase tracking-widest mb-4">B. QR Pemilik & Lokasi</p>
                    <div class="flex justify-center mb-4">
                        <div class="p-3 bg-white rounded-xl shadow-sm">
                            {!! QrCode::size(160)->generate(route('scan.owner', $product->url_token)) !!}
                        </div>
                    </div>
                    <p class="text-[10px] text-[#768c75] italic">Scan untuk melihat Nama Pemegang & Kantor</p>
                </div>
            </div>

            <div class="mt-8 text-[10px] text-gray-300 uppercase tracking-[0.2em]">
                BSH Asset Management System &bull; Digital Identity
            </div>
        </div>
    </div>

</body>
</html>
