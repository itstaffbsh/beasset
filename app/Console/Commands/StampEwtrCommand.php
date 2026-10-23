<?php

namespace App\Console\Commands;

use App\Http\Controllers\EwtrController;
use App\Models\Ewtr;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class StampEwtrCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ewtr:restamp {id? : ID atau Nomor EWTR tertentu (opsional)} {--force : Paksa stamp ulang seluruh file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stamp ulang catatan (Review & Comments) pada PDF dokumen EWTR menggunakan script Python otomatis';

    /**
     * Execute the console command.
     */
    public function handle(EwtrController $controller)
    {
        $idOrNomor = $this->argument('id');
        $force = (bool) $this->option('force');

        $query = Ewtr::query();
        if ($idOrNomor) {
            $query->where('id', $idOrNomor)->orWhere('nomor_ewtr', $idOrNomor);
        }

        $ewtrs = $query->get();

        if ($ewtrs->isEmpty()) {
            $this->warn('Tidak ada dokumen EWTR yang ditemukan.');
            return 0;
        }

        $this->info("Memproses " . $ewtrs->count() . " dokumen EWTR...");

        $successCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        foreach ($ewtrs as $ewtr) {
            $keterangan = trim($ewtr->keterangan ?? '');
            if ($keterangan === '') {
                $this->line("- [SKIP] EWTR {$ewtr->nomor_ewtr}: Tidak ada catatan/keterangan.");
                $skippedCount++;
                continue;
            }

            if (!$ewtr->pdf_path) {
                $this->line("- [SKIP] EWTR {$ewtr->nomor_ewtr}: Belum ada file PDF.");
                $skippedCount++;
                continue;
            }

            $stampedFullPath = Storage::disk('public')->path($ewtr->pdf_path);
            $rawRelPath = str_replace('.pdf', '_raw.pdf', $ewtr->pdf_path);
            $rawFullPath = Storage::disk('public')->path($rawRelPath);

            $sourcePath = file_exists($rawFullPath) ? $rawFullPath : $stampedFullPath;

            if (!file_exists($sourcePath)) {
                $this->error("- [FAIL] EWTR {$ewtr->nomor_ewtr}: File fisik sumber tidak ditemukan di {$sourcePath}");
                $failedCount++;
                continue;
            }

            // Jika tidak force, cek apakah sudah pernah distamp
            if (!$force && file_exists($rawFullPath) && file_exists($stampedFullPath)) {
                if (filesize($stampedFullPath) > filesize($rawFullPath)) {
                    $this->line("- [SKIP] EWTR {$ewtr->nomor_ewtr}: File sudah memiliki stamp catatan.");
                    $skippedCount++;
                    continue;
                }
            }

            $tempOut = tempnam(sys_get_temp_dir(), 'ewtr_artisan_stamp_') . '.pdf';
            $ok = $controller->stampPdfWithKeterangan($sourcePath, $tempOut, $keterangan);

            if ($ok && file_exists($tempOut) && filesize($tempOut) > 0) {
                copy($tempOut, $stampedFullPath);
                @unlink($tempOut);
                $this->info("✔ [OK] EWTR {$ewtr->nomor_ewtr}: Catatan berhasil di-stamp!");
                $successCount++;
            } else {
                $this->error("✖ [FAIL] EWTR {$ewtr->nomor_ewtr}: Gagal melakukan stamping catatan.");
                $failedCount++;
            }
        }

        $this->newLine();
        $this->info("Selesai! Sukses: {$successCount}, Lewati: {$skippedCount}, Gagal: {$failedCount}");
        return $failedCount > 0 ? 1 : 0;
    }
}
