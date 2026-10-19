<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Office;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    protected string $duplicateAction; // 'use_existing', 'create_new', 'cancel'

    public function __construct(string $duplicateAction = 'use_existing')
    {
        $this->duplicateAction = $duplicateAction;
    }

    public function model(array $row)
    {
        // Bersihkan dan ambil kolom sesuai format:
        // No, Full Name, Email BSH, Email, Organization, Job Position, Job Level, Join Date, Nomor HP, Area
        $empId = isset($row['no']) ? trim((string)$row['no']) : (isset($row['employee_id']) ? trim((string)$row['employee_id']) : null);
        $name = trim($row['full_name'] ?? $row['name'] ?? $row['nama_lengkap'] ?? $row['nama'] ?? '');
        $emailBsh = trim($row['email_bsh'] ?? $row['email_kantor'] ?? '');
        $personalEmail = trim($row['email'] ?? $row['email_pribadi'] ?? $row['personal_email'] ?? '');
        $orgName = trim($row['organization'] ?? $row['organisasi'] ?? $row['department'] ?? $row['departemen'] ?? '');
        $jobPos = trim($row['job_position'] ?? $row['jabatan'] ?? $row['posisi'] ?? '');
        $jobLvl = trim($row['job_level'] ?? $row['level'] ?? '');
        $rawJoinDate = $row['join_date'] ?? $row['tanggal_masuk'] ?? $row['tgl_masuk'] ?? null;
        $phone = trim($row['nomor_hp'] ?? $row['no_hp'] ?? $row['phone_number'] ?? $row['phone'] ?? $row['telepon'] ?? '');
        $areaName = trim($row['area'] ?? $row['kantor'] ?? $row['office'] ?? $row['lokasi'] ?? '');
        $address = trim($row['alamat'] ?? $row['address'] ?? '');
        $photo = trim($row['foto'] ?? $row['photo'] ?? $row['avatar'] ?? '');

        // Abaikan baris kosong tanpa nama dan tanpa email
        if (empty($name) && empty($emailBsh) && empty($personalEmail) && empty($empId)) {
            return null;
        }

        // Tentukan email utama untuk login
        $primaryEmail = !empty($emailBsh) ? $emailBsh : (!empty($personalEmail) ? $personalEmail : null);

        // Jika tidak ada email sama sekali, buatkan email otomatis berbasis employee_id atau nama
        if (empty($primaryEmail)) {
            $slug = Str::slug($empId ?: $name ?: uniqid(), '.');
            $primaryEmail = $slug . '@balibsh.com';
        }

        // Cari atau buat Organisasi / Departemen jika diisi
        $deptId = null;
        if (!empty($orgName)) {
            $dept = Department::where('nama_departemen', 'like', $orgName)->first();
            if (!$dept) {
                $words = explode(' ', $orgName);
                $code = '';
                foreach ($words as $w) {
                    if (!empty($w)) $code .= strtoupper($w[0]);
                }
                if (strlen($code) < 2) {
                    $code = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $orgName), 0, 3));
                }
                $dept = Department::create([
                    'nama_departemen' => $orgName,
                    'kode_asset'      => $code ?: 'DEP',
                ]);
            }
            $deptId = $dept->id;
        }

        // Cari atau buat Kantor / Area jika diisi
        $officeId = null;
        if (!empty($areaName)) {
            $office = Office::where('nama_kantor', 'like', $areaName)->first();
            if (!$office) {
                $office = Office::create(['nama_kantor' => $areaName]);
            }
            $officeId = $office->id;
        }
        if (!$officeId) {
            $defaultOffice = Office::first();
            $officeId = $defaultOffice ? $defaultOffice->id : 1;
        }

        // Format tanggal bergabung
        $joinDate = $this->transformDate($rawJoinDate);

        // Data yang akan disimpan
        $userData = [
            'employee_id'    => !empty($empId) ? $empId : null,
            'name'           => !empty($name) ? $name : ($empId ?: 'Karyawan BSH'),
            'email'          => $primaryEmail,
            'email_bsh'      => !empty($emailBsh) ? $emailBsh : null,
            'personal_email' => !empty($personalEmail) ? $personalEmail : null,
            'role'           => strtolower($row['role'] ?? 'user'),
            'job_position'   => !empty($jobPos) ? $jobPos : null,
            'job_level'      => !empty($jobLvl) ? $jobLvl : null,
            'join_date'      => $joinDate,
            'phone_number'   => !empty($phone) ? $phone : null,
            'phone'          => !empty($phone) ? $phone : null,
            'address'        => !empty($address) ? $address : null,
            'office_id'      => $officeId,
            'department_id'  => $deptId,
        ];

        if (!empty($photo)) {
            $userData['avatar'] = $photo;
        }

        // 1. Cari user yang sudah ada berdasarkan email utama terlebih dahulu (karena email bersifat unique)
        $user = null;
        if (!empty($primaryEmail)) {
            $user = User::where('email', $primaryEmail)
                ->orWhere('email_bsh', $primaryEmail)
                ->orWhere('personal_email', $primaryEmail)
                ->first();
        }

        // 2. Jika tidak ditemukan lewat email, coba cari berdasarkan employee_id jika diisi
        if (!$user && !empty($empId)) {
            $user = User::where('employee_id', $empId)->first();
        }

        // 3. Penanganan Duplikat Berdasarkan duplicateAction
        if ($user) {
            if ($this->duplicateAction === 'cancel') {
                throw new \Exception("Import dibatalkan karena terdapat user yang sudah ada: {$name} ({$primaryEmail}).");
            }

            if ($this->duplicateAction === 'create_new') {
                // User memilih untuk menambahkan user baru:
                // Buat email unik agar tidak terjadi SQL duplicate entry constraint violation
                $uniqueEmail = $primaryEmail;
                $parts = explode('@', $primaryEmail);
                $local = $parts[0];
                $domain = $parts[1] ?? 'balibsh.com';
                $counter = 1;
                while (User::where('email', $uniqueEmail)->exists()) {
                    $uniqueEmail = "{$local}.new{$counter}@{$domain}";
                    $counter++;
                }

                $userData['email'] = $uniqueEmail;
                $userData['email_bsh'] = $uniqueEmail;
                $userData['password'] = Hash::make('password123');
                User::create($userData);
                return null;
            }

            // Default: 'use_existing' (Gunakan & perbarui data yang ada)
            // Pastikan tidak mengubah email ke email user lain yang sudah dipakai
            if ($user->email !== $primaryEmail) {
                $otherUser = User::where('email', $primaryEmail)->where('id', '!=', $user->id)->first();
                if ($otherUser) {
                    $userData['email'] = $user->email; // Pertahankan email yang sudah aman
                }
            }
            $user->update($userData);
            return null;
        }

        // 4. Jika user benar-benar baru
        $userData['password'] = Hash::make('password123');
        User::create($userData);
        return null;
    }

    /**
     * Konversi tanggal dari format Excel atau string teks
     */
    private function transformDate($value)
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
