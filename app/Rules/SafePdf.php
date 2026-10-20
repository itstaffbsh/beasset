<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use setasign\Fpdi\Fpdi;

class SafePdf implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('File yang diupload harus berupa file yang valid.');
            return;
        }

        if (!$value->isValid()) {
            $fail('File gagal diupload atau mengalami kerusakan saat pengiriman.');
            return;
        }

        // 1. Validasi ekstensi
        $ext = strtolower($value->getClientOriginalExtension());
        if ($ext !== 'pdf') {
            $fail('File harus memiliki ekstensi .pdf yang valid.');
            return;
        }

        // 2. Validasi MIME type
        $mime = $value->getMimeType();
        $allowedMimes = [
            'application/pdf',
            'application/x-pdf',
            'application/acrobat',
            'applications/vnd.pdf',
            'text/pdf',
            'text/x-pdf',
            'application/octet-stream',
        ];
        if (!in_array($mime, $allowedMimes)) {
            $fail('Tipe file bukan merupakan dokumen PDF yang sah.');
            return;
        }

        $realPath = $value->getRealPath();
        if (!$realPath || !file_exists($realPath) || filesize($realPath) === 0) {
            $fail('File PDF kosong atau tidak dapat diakses.');
            return;
        }

        // 3. Validasi Magic Bytes (Header PDF)
        $handle = fopen($realPath, 'rb');
        if (!$handle) {
            $fail('Tidak dapat membaca file PDF.');
            return;
        }
        $header = fread($handle, 1024);
        fclose($handle);

        if ($header === false || !str_contains($header, '%PDF-')) {
            $fail('Format file tidak valid. Dokumen tidak memiliki header identitas resmi PDF (%PDF-).');
            return;
        }

        // 4. Deteksi Script dan Objek Berbahaya (Malicious payload detection)
        $content = file_get_contents($realPath);
        if ($content === false) {
            $fail('Gagal membaca isi dokumen PDF.');
            return;
        }

        // Normalisasi hex encoding dalam nama PDF (misal: /#4a#61#76#61#53#63#72#69#70#74 -> /JavaScript)
        $normalizedContent = preg_replace_callback('/#([0-9a-fA-F]{2})/', function ($matches) {
            return chr(hexdec($matches[1]));
        }, $content);

        // Daftar pola berbahaya yang tidak boleh ada dalam dokumen PDF standar
        $dangerousPatterns = [
            'Embedded JavaScript' => '/\/(?:JavaScript|JS)\b/i',
            'Launch External Program' => '/\/Launch\b/i',
            'Embedded File Attachment' => '/\/(?:EmbeddedFiles?)\b/i',
            'External Data Submission' => '/\/(?:SubmitForm|ImportData)\b/i',
            'PHP Code Tag' => '/<\?(?:php|=)/i',
            'HTML/Script Tag' => '/<script[\s>]/i',
        ];

        foreach ($dangerousPatterns as $label => $pattern) {
            if (preg_match($pattern, $normalizedContent) || preg_match($pattern, $content)) {
                $fail("Dokumen PDF ditolak demi keamanan karena terdeteksi mengandung script atau objek interaktif berisiko ({$label}). Harap upload dokumen PDF standar.");
                return;
            }
        }

        // 5. Validasi Struktur & Integritas Dokumen via Parser FPDI
        try {
            $fpdi = new Fpdi();
            $pageCount = $fpdi->setSourceFile($realPath);
            if ($pageCount < 1) {
                $fail('Dokumen PDF tidak memiliki halaman yang valid.');
                return;
            }
        } catch (\setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException $e) {
            // FPDI free parser tidak mendukung kompresi cross-reference stream (/ObjStm atau /XRef) pada PDF 1.5+.
            // Ini adalah fitur kompresi standar dokumen PDF modern, verifikasi integritas struktur dokumen.
            if (!$this->isValidPdfStructure($realPath, $content)) {
                $fail('Dokumen PDF rusak atau tidak sesuai spesifikasi dokumen standar.');
                return;
            }
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (
                str_contains($msg, 'compression technique which is not supported by the free parser') ||
                str_contains($msg, 'PDF version') ||
                str_contains($msg, 'filter') ||
                str_contains($msg, 'parser')
            ) {
                if (!$this->isValidPdfStructure($realPath, $content)) {
                    $fail('Dokumen PDF rusak atau tidak sesuai spesifikasi dokumen standar.');
                    return;
                }
            } else {
                $fail('Dokumen PDF rusak, dipalsukan, atau tidak sesuai spesifikasi dokumen standar.');
                return;
            }
        }
    }

    /**
     * Memeriksa integritas struktur dasar dokumen PDF jika tidak dapat dibaca oleh FPDI free parser
     * karena menggunakan kompresi modern cross-reference stream (/ObjStm atau /XRef).
     */
    private function isValidPdfStructure(string $realPath, string $content): bool
    {
        if (filesize($realPath) < 32 || strlen($content) < 32) {
            return false;
        }

        // 1. Header: harus ada %PDF- dalam 1024 bytes pertama
        $headerChunk = substr($content, 0, 1024);
        if (!str_contains($headerChunk, '%PDF-')) {
            return false;
        }

        // 2. Trailer: harus ada %%EOF pada bagian akhir dokumen (4096 bytes terakhir)
        $tailChunk = substr($content, -4096);
        if (!str_contains($tailChunk, '%%EOF')) {
            return false;
        }

        // 3. Harus memiliki tabel atau stream referensi silang (startxref atau /XRef)
        if (!str_contains($tailChunk, 'startxref') && !preg_match('/\/XRef\b/i', $content)) {
            return false;
        }

        // 4. Harus memuat katalog atau pohon halaman dokumen
        $hasCatalog = preg_match('/\/Type\s*\/Catalog|\/Root\b/i', $content);
        $hasPages = preg_match('/\/Type\s*\/Pages|\/Pages\b/i', $content);
        if (!$hasCatalog && !$hasPages) {
            return false;
        }

        // 5. Harus memiliki objek PDF (obj ... endobj atau stream objek)
        if (!str_contains($content, 'obj')) {
            return false;
        }

        return true;
    }
}
