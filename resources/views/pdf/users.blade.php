<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Data Karyawan</title>
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
            padding: 2px 5px;
            font-size: 6.5pt;
            font-weight: bold;
            border-radius: 3px;
            background-color: #e2e8f0;
            color: #334155;
        }
        .badge-admin { background-color: #dbeafe; color: #1e40af; }
        .badge-super { background-color: #f3e8ff; color: #6b21a8; }
        .footer {
            margin-top: 15px;
            font-size: 7pt;
            color: #888888;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="title">Daftar Data Karyawan</div>
                    <div class="subtitle">AssetCore Enterprise • PT Bali Sukses Harmonis (BSH)</div>
                </td>
                <td class="meta">
                    <div>Dicetak pada: {{ date('d M Y, H:i') }} WIB</div>
                    <div>Total Data: <strong>{{ count($users) }}</strong> Karyawan</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th style="width: 50px;">ID Karyawan</th>
                <th style="width: 110px;">Nama Lengkap</th>
                <th style="width: 120px;">Email BSH</th>
                <th style="width: 115px;">Email Pribadi</th>
                <th style="width: 90px;">Organisasi</th>
                <th style="width: 85px;">Jabatan</th>
                <th style="width: 60px;">Level</th>
                <th style="width: 60px;">Tgl Masuk</th>
                <th style="width: 75px;">No. HP</th>
                <th style="width: 65px;">Area</th>
                <th class="text-center" style="width: 45px;">Role</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $u)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $u->employee_id ?: '-' }}</td>
                    <td><strong>{{ $u->name }}</strong></td>
                    <td>{{ $u->email_bsh ?: $u->email }}</td>
                    <td>{{ $u->personal_email ?: '-' }}</td>
                    <td>{{ $u->department->nama_departemen ?? '-' }}</td>
                    <td>{{ $u->job_position ?: '-' }}</td>
                    <td>{{ $u->job_level ?: '-' }}</td>
                    <td>{{ $u->join_date ? date('d/m/Y', strtotime($u->join_date)) : '-' }}</td>
                    <td>{{ $u->phone ?: ($u->phone_number ?: '-') }}</td>
                    <td>{{ $u->office->nama_kantor ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $u->role === 'super_admin' ? 'badge-super' : ($u->role === 'admin' ? 'badge-admin' : '') }}">
                            {{ $u->role }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 20px; color: #999;">Tidak ada data karyawan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak secara otomatis oleh Sistem AssetCore Enterprise
    </div>
</body>
</html>
