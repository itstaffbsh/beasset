<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Inventaris Aset</title>
    <style>
        @page {
            margin: 15mm 10mm 15mm 10mm;
            size: a4 landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .header {
            margin-bottom: 12px;
            border-bottom: 2px solid #2d3e50;
            padding-bottom: 8px;
        }
        .header table {
            width: 100%;
        }
        .title {
            font-size: 14pt;
            font-weight: bold;
            color: #2d3e50;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            font-size: 8pt;
            color: #666666;
            margin-top: 2px;
        }
        .meta {
            text-align: right;
            font-size: 7.5pt;
            color: #555555;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table.data-table th {
            background-color: #2d3e50;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 5px;
            font-size: 7.5pt;
            border: 1px solid #2d3e50;
            text-transform: uppercase;
        }
        table.data-table td {
            padding: 5px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            font-size: 7.5pt;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 6.5pt;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-borrowed { background-color: #dbeafe; color: #1e40af; }
        .badge-ready { background-color: #dcfce7; color: #166534; }
        .footer {
            margin-top: 15px;
            font-size: 7pt;
            color: #888888;
            text-align: right;
        }
        .specs {
            font-size: 7pt;
            color: #555555;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="title">Daftar Inventaris Aset IT</div>
                    <div class="subtitle">AssetCore Enterprise • PT Bali Sukses Harmonis (BSH)</div>
                </td>
                <td class="meta">
                    <div>Dicetak pada: {{ date('d M Y, H:i') }} WIB</div>
                    <div>Total Aset: <strong>{{ count($products) }}</strong> Unit</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th style="width: 70px;">ID Barang</th>
                <th style="width: 65px;">Kategori</th>
                <th style="width: 140px;">Nama / Deskripsi Aset</th>
                <th style="width: 75px;">Serial Number</th>
                <th style="width: 110px;">Pemegang Saat Ini</th>
                <th style="width: 55px;" class="text-center">Status</th>
                <th style="width: 140px;">Spesifikasi</th>
                <th style="width: 80px;">Catatan / Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $p)
                @php
                    $lastHistory = $p->histories->first();
                    $isBorrowed = $lastHistory && $lastHistory->receiver && empty($lastHistory->returned_date);
                    $specs = [];
                    if ($p->processor) $specs[] = 'Proc: ' . $p->processor;
                    if ($p->ram) $specs[] = 'RAM: ' . $p->ram;
                    if ($p->ssd) $specs[] = 'SSD: ' . $p->ssd;
                    if ($p->os) $specs[] = 'OS: ' . $p->os;
                    if ($p->screen_id) $specs[] = 'Screen: ' . $p->screen_id;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold; color: #1e3a8a;">
                        {{ $p->full_nomor_unik }}
                    </td>
                    <td>{{ $p->classification->nama_klasifikasi ?? '-' }}</td>
                    <td><strong>{{ $p->description }}</strong></td>
                    <td style="font-family: monospace;">{{ $p->serial_number ?: '-' }}</td>
                    <td>
                        @if($isBorrowed)
                            <strong>{{ $lastHistory->receiver->name }}</strong>
                            <div style="font-size: 6.5pt; color: #666;">Tgl Pinjam: {{ $lastHistory->lend_date ? date('d/m/Y', strtotime($lastHistory->lend_date)) : '-' }}</div>
                        @else
                            <span style="color: #6b7280;">Tersedia (Gudang)</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $isBorrowed ? 'badge-borrowed' : 'badge-ready' }}">
                            {{ $isBorrowed ? 'Dipinjam' : 'Ready' }}
                        </span>
                    </td>
                    <td class="specs">
                        {{ !empty($specs) ? implode(' • ', $specs) : '-' }}
                    </td>
                    <td style="font-size: 7pt; color: #666;">
                        {{ $p->remarks ?: ($lastHistory->remarks ?? '-') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #999;">Tidak ada data inventaris aset.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh Sistem AssetCore Enterprise
    </div>
</body>
</html>
