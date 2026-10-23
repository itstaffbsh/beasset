<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Office;
use App\Models\Department;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToCollection, WithHeadingRow
{
    public string $duplicateAction; // 'use_existing', 'create_new', 'cancel'
    public int $importedCount = 0;
    public int $updatedCount = 0;

    protected array $offices = [];
    protected array $departments = [];
    protected ?string $defaultOfficeId = null;
    protected array $emailMap = [];
    protected array $empIdMap = [];
    protected string $defaultPassword;
    protected int $nextEmpNum;

    public function __construct(string $duplicateAction = 'use_existing')
    {
        $this->duplicateAction = $duplicateAction;
        $this->initializeCache();
    }

    /**
     * Preload dan cache data yang dibutuhkan ke dalam memory
     * untuk menghindari ribuan query individual ke database saat memproses baris Excel.
     */
    protected function initializeCache(): void
    {
        // Pre-compute bcrypt password satu kali untuk menghemat beban CPU loop
        $this->defaultPassword = Hash::make('password123');

        $defaultOffice = Office::first();
        $this->defaultOfficeId = $defaultOffice ? $defaultOffice->id : null;

        // Cache offices
        foreach (Office::all() as $office) {
            $this->offices[strtolower(trim($office->nama_kantor))] = $office->id;
        }

        // Cache departments
        foreach (Department::all() as $dept) {
            $this->departments[strtolower(trim($dept->nama_departemen))] = $dept->id;
        }

        // Cache users yang sudah ada
        $users = User::all();
        foreach ($users as $u) {
            if ($u->email) {
                $this->emailMap[strtolower(trim($u->email))] = $u;
            }
            if ($u->email_bsh) {
                $this->emailMap[strtolower(trim($u->email_bsh))] = $u;
            }
            if ($u->personal_email) {
                $this->emailMap[strtolower(trim($u->personal_email))] = $u;
            }
            if ($u->employee_id) {
                $this->empIdMap[trim($u->employee_id)] = $u;
            }
        }

        // Hitung ID karyawan numerik tertinggi berikutnya
        $maxNum = User::whereRaw("employee_id REGEXP '^[0-9]+$'")->max(DB::raw('CAST(employee_id AS UNSIGNED)'));
        $this->nextEmpNum = $maxNum ? (int)$maxNum + 1 : 1;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $this->processRow($row->toArray());
            }
        });
    }

    protected function processRow(array $row): void
    {
        // Bersihkan dan ambil kolom sesuai format
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
            return;
        }

        // Tentukan email utama untuk login
        $primaryEmail = !empty($emailBsh) ? $emailBsh : (!empty($personalEmail) ? $personalEmail : null);
        if (empty($primaryEmail)) {
            $slug = Str::slug($empId ?: $name ?: uniqid(), '.');
            $primaryEmail = $slug . '@balibsh.com';
        }

        // Cari atau buat Kantor / Area jika diisi
        $officeId = null;
        if (!empty($areaName)) {
            $lowerArea = strtolower($areaName);
            if (isset($this->offices[$lowerArea])) {
                $officeId = $this->offices[$lowerArea];
            } else {
                $office = Office::create(['nama_kantor' => $areaName]);
                $this->offices[$lowerArea] = $office->id;
                $officeId = $office->id;
            }
        }
        if (!$officeId) {
            $officeId = $this->defaultOfficeId;
        }

        // Cari atau buat Organisasi / Departemen jika diisi
        $deptId = null;
        if (!empty($orgName)) {
            $lowerDept = strtolower($orgName);
            if (isset($this->departments[$lowerDept])) {
                $deptId = $this->departments[$lowerDept];
            } else {
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
                $this->departments[$lowerDept] = $dept->id;
                $deptId = $dept->id;
            }
        }

        // Format tanggal bergabung
        $joinDate = $this->transformDate($rawJoinDate);

        // Data yang akan disimpan
        $userData = [
            'employee_id'    => !empty($empId) ? $empId : null,
            'name'           => !empty($name) ? $name : ($empId ?: 'Karyawan BSH'),
            'email'          => $primaryEmail,
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

        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'email_bsh')) {
            $userData['email_bsh'] = !empty($emailBsh) ? $emailBsh : null;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'personal_email')) {
            $userData['personal_email'] = !empty($personalEmail) ? $personalEmail : null;
        }

        if (!empty($photo)) {
            $userData['avatar'] = $photo;
        }

        // Cari user yang sudah ada berdasarkan email utama atau employee_id
        $lowerEmail = strtolower($primaryEmail);
        $existingUser = $this->emailMap[$lowerEmail] ?? ($empId && isset($this->empIdMap[$empId]) ? $this->empIdMap[$empId] : null);

        if ($existingUser) {
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
                while (isset($this->emailMap[strtolower($uniqueEmail)])) {
                    $uniqueEmail = "{$local}.new{$counter}@{$domain}";
                    $counter++;
                }

                $userData['email'] = $uniqueEmail;
                if (!empty($userData['email_bsh'])) {
                    $userData['email_bsh'] = $uniqueEmail;
                }

                // Pastikan employee_id unik juga
                if (!empty($userData['employee_id']) && isset($this->empIdMap[$userData['employee_id']])) {
                    $userData['employee_id'] = str_pad($this->nextEmpNum++, 5, '0', STR_PAD_LEFT);
                } elseif (empty($userData['employee_id'])) {
                    $userData['employee_id'] = str_pad($this->nextEmpNum++, 5, '0', STR_PAD_LEFT);
                }

                $userData['password'] = $this->defaultPassword;
                $newUser = User::create($userData);

                $this->emailMap[strtolower($userData['email'])] = $newUser;
                if (!empty($userData['employee_id'])) {
                    $this->empIdMap[$userData['employee_id']] = $newUser;
                }
                $this->importedCount++;
                return;
            }

            // Default 'use_existing': Update user yang sudah ada
            if ($existingUser->email !== $primaryEmail) {
                // Cek apakah email baru sudah dipakai oleh user lain di database
                if (isset($this->emailMap[$lowerEmail]) && $this->emailMap[$lowerEmail]->id !== $existingUser->id) {
                    $userData['email'] = $existingUser->email; // Pertahankan email yang sudah ada
                }
            }

            $existingUser->update($userData);

            $this->emailMap[strtolower($existingUser->email)] = $existingUser;
            if (!empty($existingUser->employee_id)) {
                $this->empIdMap[$existingUser->employee_id] = $existingUser;
            }
            $this->updatedCount++;
            return;
        }

        // User baru
        if (empty($userData['employee_id'])) {
            $userData['employee_id'] = str_pad($this->nextEmpNum++, 5, '0', STR_PAD_LEFT);
        }

        $userData['password'] = $this->defaultPassword;
        $newUser = User::create($userData);

        $this->emailMap[strtolower($userData['email'])] = $newUser;
        if (!empty($userData['employee_id'])) {
            $this->empIdMap[$userData['employee_id']] = $newUser;
        }
        $this->importedCount++;
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
