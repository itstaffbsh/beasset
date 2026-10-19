<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            background: #f0f0f0;
        }

        /* ── Wrapper halaman ── */
        .page-wrapper {
            max-width: 794px;
            margin: 20px auto;
            background: white;
            padding: 32px 40px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.15);
            position: relative;
        }

        /* ── Tombol cetak (tidak ikut tercetak) ── */
        .print-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #4a554a;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-family: Arial, sans-serif;
        }
        .print-bar a { color: #c8dfc6; text-decoration: none; font-size: 13px; }
        .btn-print {
            background: #a47b53;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            font-family: Arial, sans-serif;
        }
        .btn-print:hover { background: #8b6540; }

        /* ── HEADER & FOOTER IMAGES ── */
        .header-image, .footer-image {
            width: 100%;
            height: auto;
            display: block;
        }
        .header-surat {
            margin: -32px -40px 20px -40px;
            width: calc(100% + 80px);
            max-width: none;
        }
        .footer-surat {
            margin-top: 30px;
        }

        /* ── JUDUL ── */
        .judul-surat {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            font-family:calibri,sans-serif ;
            margin: 25px 0 30px;
            letter-spacing: 1px;
        }

        /* ── BODY TEKS ── */
        .body-text { line-height: 1.5; text-align: justify; margin-bottom: 6px; }
        .body-text .indent { display: inline-block; width: 24px; }
        .pihak-block { margin: 4px 0 4px 20px; }
        .pihak-row { display: flex; gap: 0; margin-bottom: 2px; }
        .pihak-label { width: 90px; flex-shrink: 0; }
        .pihak-value { flex: 1; }

        /* ── TABEL BARANG ── */
        .tabel-barang {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 10.5pt;
        }
        .tabel-barang th, .tabel-barang td {
            border: 1px solid #000;
            padding: 7px 10px;
            vertical-align: top;
        }
        .tabel-barang th {
            text-align: center;
            font-weight: bold;
            background: #fff;
        }
        .tabel-barang td:first-child,
        .tabel-barang td:nth-child(3),
        .tabel-barang td:nth-child(4) { text-align: center; }

        /* ── PERSYARATAN ── */
        .persyaratan { margin: 8px 0; line-height: 1.5; }
        .persyaratan ol { padding-left: 20px; }

        /* ── TANDA TANGAN ── */
        .ttd-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .ttd-box { text-align: center; width: 45%; }
        .ttd-box .ttd-label { font-weight: bold; margin-bottom: 2px; }
        .ttd-box .ttd-pihak { font-size: 10pt; margin-bottom: 75px; }
        .ttd-box .ttd-nama { font-weight: bold; border-top: 1px solid #000; padding-top: 4px; display: inline-block; min-width: 180px; }

        /* ── FOOTER ── */
        .footer-container {
            margin: 30px -40px -32px -40px;
            position: relative;
        }

        /* ── PRINT STYLES ── */
        @media print {
            @page { 
                size: A4;
                margin: 0; 
            }
            body { 
                background: white; 
                margin: 0;
            }
            .page-wrapper { 
                margin: 0; 
                padding: 2.5cm 2.5cm 0 2.5cm !important; 
                box-shadow: none; 
                max-width: 100%; 
                width: 100%;
                min-height: auto;
            }
            .header-surat {
                position: fixed;
                top: 0.8cm;
                left: 0;
                width: 21cm;
                margin: 0 !important;
                z-index: 9999;
            }
            .footer-container {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 21cm;
                margin: 0 !important;
                z-index: 9999;
            }
            .print-bar { display: none !important; }
            
            /* Sembunyikan header/footer bawaan browser secara paksa */
            header, footer { display: none !important; }
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    {{-- Tombol Aksi (tidak tercetak) --}}
    <div class="print-bar">
        <a href="{{ url()->previous() }}">← Kembali</a>
        <span style="font-family: Arial; font-weight: bold; font-size: 14px;">
            📄 SURAT TANDA TERIMA BARANG — Siap Cetak
        </span>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>

    {{-- ══ HEADER ══ --}}
    <div class="header-surat">
        <img src="{{ asset('images/Picture1.png') }}" alt="Header STTB" class="header-image">
    </div>

    {{-- ══ JUDUL ══ --}}
    <div class="judul-surat">SURAT TANDA TERIMA BARANG</div>

    {{-- ══ PEMBUKAAN ══ --}}
    <p class="body-text">
        Pada hari ini {{ \Carbon\Carbon::parse($tanggal)->isoFormat('D MMMM YYYY') }}
        bertempat di PT Samasta Properti Manajemen Internasional kami yang bertanda tangan dibawah ini :
    </p>

    {{-- PIHAK PERTAMA --}}
    <div class="pihak-block">
        <div class="pihak-row"><span class="pihak-label"><span class="indent">I.</span> Nama</span><span>:</span>&nbsp;<span class="pihak-value"><strong>{{ $pihakPertama?->name ?? '-' }}</strong></span></div>
        <div class="pihak-row"><span class="pihak-label"><span class="indent"></span> Jabatan</span><span>:</span>&nbsp;<span class="pihak-value">{{ $pihakPertama?->job_position ?? ucfirst($pihakPertama?->role ?? '-') }}</span></div>
    </div>
    <p class="body-text">
        Dalam surat serah terima ini bertindak untuk dan atas nama PT Samasta Properti Manajemen Internasional
        selaku yang mana selanjutnya disebut <strong>PIHAK PERTAMA</strong>
    </p>

    {{-- PIHAK KEDUA --}}
    <div class="pihak-block">
        <div class="pihak-row"><span class="pihak-label"><span class="indent">II.</span> Nama</span><span>:</span>&nbsp;<span class="pihak-value"><strong>{{ $pihakKedua?->name ?? '-' }}</strong></span></div>
        <div class="pihak-row"><span class="pihak-label"><span class="indent"></span> Jabatan</span><span>:</span>&nbsp;<span class="pihak-value">{{ $pihakKedua?->job_position ?? 'Staff' }}</span></div>
    </div>
    <p class="body-text">
        Dalam hal ini bertindak untuk dan atas nama PT Samasta Properti Manajemen Internasional
        selaku yang mana selanjutnya akan disebut sebagai <strong>PIHAK KEDUA</strong>.
    </p>

    <p class="body-text" style="margin-top:10px;">
        Saat ini telah melakukan serah terima barang dari PIHAK PERTAMA kepada PIHAK KEDUA.
        PIHAK KEDUA telah menerima barang dari PIHAK PERTAMA sebagai berikut :
    </p>

    {{-- ══ TABEL BARANG ══ --}}
    <table class="tabel-barang">
        <thead>
            <tr>
                <th style="width:5%">NO.</th>
                <th style="width:42%">JENIS BARANG</th>
                <th style="width:13%">TANGGAL</th>
                <th style="width:10%">JUMLAH</th>
                <th style="width:30%">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($histories as $i => $history)
            <tr>
                <td>{{ $i + 1 }}.</td>
                <td>{{ $history->product?->description ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}</td>
                <td>
                    @php
                        $idBarang = $history->product?->full_nomor_unik ?? (($history->product?->department?->kode_asset ?? 'SM') . '-' . ($history->product?->nomor_unik ?? '-'));
                        $accList = [];
                        if (!empty($history->accessories)) {
                            $accList = is_array($history->accessories) ? $history->accessories : array_filter(array_map('trim', explode(',', $history->accessories)));
                        }
                        $ket = $idBarang . (!empty($accList) ? '+' . implode('+', $accList) : '');
                        if (!empty($history->catatan) && !in_array($history->catatan, $accList)) {
                            $ket .= ' (' . $history->catatan . ')';
                        }
                    @endphp
                    {{ $ket }}
                </td>
            @endforeach
        </tbody>
    </table>

    {{-- ══ KETENTUAN ══ --}}
    <p class="body-text">
        Barang-barang tersebut diatas adalah hak milik PT Samasta Properti Manajemen Internasional sepenuhnya,
        dan tidak bisa dipindah tangankan kepada pihak lain.
    </p>

    <p class="body-text" style="margin-top:10px;">
        Berikut persyaratan yang harus dipenuhi oleh Pihak Kedua antara lain:
    </p>
    <div class="persyaratan">
        <ol>
            <li>Menjaga dengan baik barang yang telah diserahkan kepada pihak kedua</li>
            <li>Tidak terkena air atau jatuh</li>
            <li>Hanya digunakan untuk keperluan pekerjaan kantor</li>
            <li>Jika terdapat damage atau kerusakan akan menjadi tanggung jawab masing-masing pihak</li>
            <li>Apabila pihak kedua mau mengundurkan diri, barang yang telah diterima agar dikembalikan dengan mengisi clearance form dari HRD</li>
        </ol>
    </div>

    <p class="body-text" style="margin-top:10px;">
        Demikian surat serah terima barang ini dibuat oleh kedua belah pihak yang bersangkutan.
        Setelah penandatanganan surat ini serah terima ini, maka semua barang yang tertera di atas
        akan menjadi tanggung jawab <strong>PIHAK KEDUA</strong>
    </p>

    {{-- ══ TANDA TANGAN ══ --}}
    <div class="ttd-section">
        <div class="ttd-box">
            <p class="ttd-label">Yang Menyerahkan</p>
            <p class="ttd-pihak">PIHAK PERTAMA</p>
            <span class="ttd-nama">{{ $pihakPertama?->name ?? '-' }}</span>
        </div>
        <div class="ttd-box">
            <p class="ttd-label">Yang Menerima</p>
            <p class="ttd-pihak">PIHAK KEDUA</p>
            <span class="ttd-nama">{{ $pihakKedua?->name ?? '-' }}</span>
        </div>
    </div>

    {{-- ══ FOOTER ══ --}}
    <div class="footer-container">
        <img src="{{ asset('images/Picture3.png') }}" alt="Footer STTB" class="footer-image">
    </div>

</div>

</body>
</html>
