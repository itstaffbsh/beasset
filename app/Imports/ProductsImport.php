<?php

namespace App\Imports;

use App\Models\Aset;
use App\Models\User;
use App\Models\Classification;
use App\Models\ProductHistory;
use App\Models\Department;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class ProductsImport implements ToModel, WithHeadingRow
{
    protected ?array $holderMappings;
    private int $rowIndex = 0;

    public function __construct(?array $holderMappings = null)
    {
        $this->holderMappings = $holderMappings;
    }

    public function model(array $row)
    {
        $currentIndex = $this->rowIndex++;

        // 1. Ambil kolom-kolom utama
        $idBarang = trim($row['id_barang'] ?? $row['nomor_unik'] ?? $row['id'] ?? '');
        $categoryName = trim($row['asset_category'] ?? $row['kategori_aset'] ?? $row['kategori'] ?? $row['classification'] ?? '');
        $description = trim($row['description'] ?? $row['nama_barang'] ?? $row['device_name'] ?? '');

        // Abaikan baris kosong total
        if (empty($idBarang) && empty($categoryName) && empty($description)) {
            return null;
        }

        // 2. Kategori Aset (Jenis Barang)
        $classification = null;
        if (!empty($categoryName)) {
            $code = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $categoryName), 0, 2)) ?: 'AS';
            $classification = Classification::firstOrCreate(
                ['nama_klasifikasi' => $categoryName],
                ['kode_klasifikasi' => $code]
            );
        } else {
            $classification = Classification::first();
        }

        // 3. ID Barang & Departemen
        $prefix = 'AC';
        $number = $idBarang;
        $department = null;

        if (str_contains($idBarang, '-')) {
            $parts = explode('-', $idBarang, 2);
            $prefix = strtoupper(trim($parts[0]));
            $number = trim($parts[1]);

            // Cari atau buat departemen dengan kode prefix tersebut (misal: AC)
            $department = Department::where('kode_asset', $prefix)->first();
            if (!$department) {
                $department = Department::create([
                    'nama_departemen' => $prefix,
                    'kode_asset'      => $prefix,
                ]);
            }
        } else {
            // Default ke departemen pertama jika tidak ada format prefix strip
            $department = Department::first();
            if (!$department) {
                $department = Department::create([
                    'nama_departemen' => 'General',
                    'kode_asset'      => 'AC',
                ]);
            }
        }

        // 4. Update atau Buat Aset
        $product = null;
        if (!empty($number)) {
            $product = Aset::where('department_id', $department->id)
                ->where('nomor_unik', $number)
                ->first();
        }

        if (!$product) {
            $product = new Aset();
            $product->department_id = $department->id;
            $product->nomor_unik = $number;
            $product->url_token = Str::random(40);
        }

        // Field spesifikasi (bebas dikosongkan jika tidak ada)
        $processor = $row['proccessor'] ?? $row['processor'] ?? null;
        $ram = $row['ram'] ?? null;
        $newRam = $row['new_ram'] ?? null;
        $ssd = $row['ssd'] ?? null;
        $newSsd = $row['new_ssd'] ?? null;
        $os = $row['os'] ?? null;
        $screenId = $row['screen_id'] ?? null;
        $deviceName = $row['device_name'] ?? null;
        $serialNumber = $row['serial_number'] ?? null;
        $imei = $row['imei'] ?? null;
        $remarks = $row['remaks'] ?? $row['remarks'] ?? $row['catatan'] ?? null;
        $macAddress = $row['mac_address'] ?? null;

        $product->description = !empty($description) ? $description : ($deviceName ?: ($categoryName ?: 'Asset'));
        $product->classification_id = $classification?->id;
        $product->processor = !empty($processor) ? trim((string)$processor) : null;
        $product->ram = !empty($ram) ? trim((string)$ram) : null;
        $product->new_ram = !empty($newRam) ? trim((string)$newRam) : null;
        $product->ssd = !empty($ssd) ? trim((string)$ssd) : null;
        $product->new_ssd = !empty($newSsd) ? trim((string)$newSsd) : null;
        $product->os = !empty($os) ? trim((string)$os) : null;
        $product->screen_id = !empty($screenId) ? trim((string)$screenId) : null;
        $product->device_name = !empty($deviceName) ? trim((string)$deviceName) : null;
        $product->serial_number = !empty($serialNumber) ? trim((string)$serialNumber) : null;
        $product->device_class = null; // Diabaikan / tidak dimasukkan
        $product->imei = !empty($imei) ? trim((string)$imei) : null;
        $product->remarks = !empty($remarks) ? trim((string)$remarks) : null;
        $product->mac_address = !empty($macAddress) ? trim((string)$macAddress) : null;
        $product->save();

        // 5. Pemegang / Holder & Riwayat Pinjam
        $holderName = trim($row['name'] ?? $row['full_name'] ?? $row['pemegang'] ?? '');
        $rawLendDate = $row['lend_date'] ?? $row['tanggal_pinjam'] ?? null;
        $rawReturnedDate = $row['returned_date'] ?? $row['tanggal_kembali'] ?? null;

        $targetUser = null;

        if ($this->holderMappings !== null) {
            // Jika menggunakan hasil verifikasi modal
            $mapping = $this->holderMappings[$currentIndex] 
                ?? $this->holderMappings[(string)$currentIndex] 
                ?? $this->holderMappings[$idBarang] 
                ?? $this->holderMappings[$number] 
                ?? null;

            if ($mapping) {
                $isMatch = is_array($mapping) ? ($mapping['is_match'] ?? false) : false;
                $userId = is_array($mapping) ? ($mapping['user_id'] ?? null) : $mapping;

                if ($isMatch && !empty($userId)) {
                    $targetUser = User::find($userId);
                }
                // Jika is_match false (tombol "Tidak Sama" diklik), $targetUser tetap null
            }
        } else {
            // Default fallback jika tanpa modal: cek apakah bukan kata kosong / hilang
            $clean = strtolower($holderName);
            $ignored = ['-', 'kosong', 'hilang', 'stock', 'stok', 'di kantor', 'dikantor', 'kantor', 'rusak', 'tersedia', 'null', 'none'];
            if (!empty($holderName) && !in_array($clean, $ignored)) {
                $candidateEmail = Str::slug($holderName, '.') . '@balibsh.com';
                $targetUserQuery = User::where('name', 'like', $holderName)
                    ->orWhere('email', $candidateEmail);
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'email_bsh')) {
                    $targetUserQuery->orWhere('email_bsh', $candidateEmail);
                }
                $targetUser = $targetUserQuery->first();

                if (!$targetUser) {
                    $newUserData = [
                        'name'          => $holderName,
                        'email'         => $candidateEmail,
                        'role'          => 'user',
                        'office_id'     => 1,
                        'department_id' => $department->id,
                    ];
                    if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'email_bsh')) {
                        $newUserData['email_bsh'] = $candidateEmail;
                    }
                    $targetUser = User::create($newUserData);
                }
            }
        }

        // Jika ada target peminjam, catat transaksi peminjaman
        if ($targetUser) {
            $lendDate = $this->transformDate($rawLendDate) ?: now()->toDateString();
            $returnedDate = $this->transformDate($rawReturnedDate);

            // Simpan riwayat peminjaman aset
            ProductHistory::create([
                'product_id'      => $product->id,
                'dioper_oleh'     => auth()->id() ?: 1,
                'diterima_oleh'   => $targetUser->id,
                'lend_date'       => $lendDate,
                'tanggal_pinjam'  => $lendDate,
                'returned_date'   => $returnedDate,
                'description'     => $product->description,
                'remarks'         => $remarks,
                'catatan'         => $remarks,
                'jenis_transaksi' => !empty($returnedDate) ? 'kembali' : 'meminjam',
            ]);
        }

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
