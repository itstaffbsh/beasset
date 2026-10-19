<?php

namespace App\Http\Controllers;

use App\Models\Ewtr;
use App\Models\EwtrItem;
use App\Models\EwtrToolCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Rules\SafePdf;

class EwtrController extends Controller
{
    public function index(Request $request)
    {
        $query = Ewtr::with(['items', 'creator']);

        // Filter by status: 'belum_sukses' or 'sukses'
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        $searchTerm = $request->get('q');
        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nomor_ewtr', 'like', "%{$searchTerm}%")
                  ->orWhere('keterangan', 'like', "%{$searchTerm}%")
                  ->orWhere('pdf_name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('items', function ($iq) use ($searchTerm) {
                      $iq->where('item_name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        $ewtrs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Also append tab summary counts
        $countBelumSukses = Ewtr::where('status', 'belum_sukses')->count();
        $countSukses = Ewtr::where('status', 'sukses')->count();

        return response()->json([
            'data' => $ewtrs->items(),
            'current_page' => $ewtrs->currentPage(),
            'last_page' => $ewtrs->lastPage(),
            'per_page' => $ewtrs->perPage(),
            'total' => $ewtrs->total(),
            'meta' => [
                'current_page' => $ewtrs->currentPage(),
                'last_page' => $ewtrs->lastPage(),
                'per_page' => $ewtrs->perPage(),
                'total' => $ewtrs->total(),
                'count_belum_sukses' => $countBelumSukses,
                'count_sukses' => $countSukses,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pdf' => ['required', 'file', 'max:25600', new SafePdf()],
            'items' => 'required|array|min:1',
            'items.*' => 'required',
            'keterangan' => 'nullable|string',
            'nomor_ewtr' => 'nullable|string|max:100',
        ]);

        $file = $request->file('pdf');
        $originalName = $file->getClientOriginalName();
        $uniqueBase = 'ewtr_' . time() . '_' . Str::random(8);
        $fileName = $uniqueBase . '.' . $file->getClientOriginalExtension();
        $rawFileName = $uniqueBase . '_raw.' . $file->getClientOriginalExtension();

        // Store raw original PDF
        $file->storeAs('ewtrs', $rawFileName, 'public');

        $rawFullPath = Storage::disk('public')->path('ewtrs/' . $rawFileName);
        $stampedFullPath = Storage::disk('public')->path('ewtrs/' . $fileName);

        // Stamp keterangan onto PDF
        $this->stampPdfWithKeterangan($rawFullPath, $stampedFullPath, $request->keterangan);

        $path = 'ewtrs/' . $fileName;

        // Generate nomor_ewtr if not provided
        $nomorEwtr = $request->nomor_ewtr;
        if (!$nomorEwtr) {
            $datePart = now()->format('Ymd');
            $latestToday = Ewtr::whereDate('created_at', now()->toDateString())->count() + 1;
            $nomorEwtr = 'EWTR-' . $datePart . '-' . str_pad($latestToday, 4, '0', STR_PAD_LEFT);
        }

        $ewtr = Ewtr::create([
            'nomor_ewtr' => $nomorEwtr,
            'pdf_path' => $path,
            'pdf_name' => $originalName,
            'keterangan' => $request->keterangan,
            'status' => 'belum_sukses',
            'created_by' => auth()->id(),
        ]);

        // Process items (array of category IDs or objects)
        $categoryIds = [];
        foreach ($request->items as $item) {
            if (is_array($item)) {
                $categoryIds[] = $item['id'] ?? $item['category_id'] ?? null;
            } else {
                $categoryIds[] = $item;
            }
        }
        $categoryIds = array_filter($categoryIds);

        $categories = EwtrToolCategory::whereIn('id', $categoryIds)->get()->keyBy('id');

        foreach ($categoryIds as $catId) {
            $cat = $categories->get($catId);
            $name = $cat ? $cat->name : "Item #{$catId}";

            EwtrItem::create([
                'ewtr_id' => $ewtr->id,
                'ewtr_tool_category_id' => $catId,
                'item_name' => $name,
                'is_completed' => false,
            ]);
        }

        $ewtr->syncStatus();
        $ewtr->load(['items', 'creator']);

        return response()->json([
            'message' => 'EWTR berhasil ditambahkan.',
            'data' => $ewtr,
        ], 201);
    }

    public function show(Ewtr $ewtr)
    {
        $ewtr->load(['items', 'creator']);
        return response()->json($ewtr);
    }

    public function update(Request $request, Ewtr $ewtr)
    {
        if ($ewtr->status === 'sukses') {
            return response()->json([
                'message' => 'Dokumen EWTR yang sudah sukses tidak dapat diedit.'
            ], 422);
        }

        $request->validate([
            'pdf' => ['nullable', 'file', 'max:25600', new SafePdf()],
            'items' => 'nullable|array|min:1',
            'keterangan' => 'nullable|string',
            'nomor_ewtr' => 'nullable|string|max:100',
        ]);

        $keteranganToUse = $request->has('keterangan') ? $request->keterangan : $ewtr->keterangan;

        if ($request->hasFile('pdf')) {
            // Delete old files
            if ($ewtr->pdf_path) {
                $oldStamped = $ewtr->pdf_path;
                $oldRaw = str_replace('.pdf', '_raw.pdf', $oldStamped);
                if (Storage::disk('public')->exists($oldStamped)) {
                    Storage::disk('public')->delete($oldStamped);
                }
                if (Storage::disk('public')->exists($oldRaw)) {
                    Storage::disk('public')->delete($oldRaw);
                }
            }

            $file = $request->file('pdf');
            $originalName = $file->getClientOriginalName();
            $uniqueBase = 'ewtr_' . time() . '_' . Str::random(8);
            $fileName = $uniqueBase . '.' . $file->getClientOriginalExtension();
            $rawFileName = $uniqueBase . '_raw.' . $file->getClientOriginalExtension();

            $file->storeAs('ewtrs', $rawFileName, 'public');

            $rawFullPath = Storage::disk('public')->path('ewtrs/' . $rawFileName);
            $stampedFullPath = Storage::disk('public')->path('ewtrs/' . $fileName);

            $this->stampPdfWithKeterangan($rawFullPath, $stampedFullPath, $keteranganToUse);

            $ewtr->pdf_path = 'ewtrs/' . $fileName;
            $ewtr->pdf_name = $originalName;
        } elseif ($request->has('keterangan') && $request->keterangan !== $ewtr->keterangan) {
            // Re-stamp PDF with new keterangan
            if ($ewtr->pdf_path && Storage::disk('public')->exists($ewtr->pdf_path)) {
                $stampedFullPath = Storage::disk('public')->path($ewtr->pdf_path);
                $rawRelPath = str_replace('.pdf', '_raw.pdf', $ewtr->pdf_path);
                $rawFullPath = Storage::disk('public')->path($rawRelPath);

                $sourceForStamping = file_exists($rawFullPath) ? $rawFullPath : $stampedFullPath;
                $tempOutput = tempnam(sys_get_temp_dir(), 'ewtr_stamped_') . '.pdf';

                $this->stampPdfWithKeterangan($sourceForStamping, $tempOutput, $request->keterangan);

                if (file_exists($tempOutput) && filesize($tempOutput) > 0) {
                    copy($tempOutput, $stampedFullPath);
                    @unlink($tempOutput);
                }
            }
        }

        if ($request->filled('nomor_ewtr')) {
            $ewtr->nomor_ewtr = $request->nomor_ewtr;
        }
        if ($request->has('keterangan')) {
            $ewtr->keterangan = $request->keterangan;
        }

        $ewtr->save();

        // Update items if provided
        if ($request->has('items') && is_array($request->items)) {
            $categoryIds = [];
            foreach ($request->items as $item) {
                if (is_array($item)) {
                    $categoryIds[] = $item['id'] ?? $item['category_id'] ?? null;
                } else {
                    $categoryIds[] = $item;
                }
            }
            $categoryIds = array_unique(array_filter($categoryIds));

            $existingItems = $ewtr->items()->get()->keyBy('ewtr_tool_category_id');
            $categories = EwtrToolCategory::whereIn('id', $categoryIds)->get()->keyBy('id');

            // Delete items that are no longer selected
            $ewtr->items()->whereNotIn('ewtr_tool_category_id', $categoryIds)->delete();

            // Add new items
            foreach ($categoryIds as $catId) {
                if (!$existingItems->has($catId)) {
                    $cat = $categories->get($catId);
                    $name = $cat ? $cat->name : "Item #{$catId}";

                    EwtrItem::create([
                        'ewtr_id' => $ewtr->id,
                        'ewtr_tool_category_id' => $catId,
                        'item_name' => $name,
                        'is_completed' => false,
                    ]);
                }
            }
        }

        $ewtr->syncStatus();
        $ewtr->load(['items', 'creator']);

        return response()->json([
            'message' => 'EWTR berhasil diperbarui.',
            'data' => $ewtr,
        ]);
    }

    public function destroy(Ewtr $ewtr)
    {
        if ($ewtr->status === 'sukses') {
            return response()->json([
                'message' => 'Dokumen EWTR yang sudah sukses tidak dapat dihapus.'
            ], 422);
        }

        // Delete PDF files (both stamped and raw)
        if ($ewtr->pdf_path) {
            $stampedPath = $ewtr->pdf_path;
            $rawPath = str_replace('.pdf', '_raw.pdf', $stampedPath);
            if (Storage::disk('public')->exists($stampedPath)) {
                Storage::disk('public')->delete($stampedPath);
            }
            if (Storage::disk('public')->exists($rawPath)) {
                Storage::disk('public')->delete($rawPath);
            }
        }

        $ewtr->delete();

        return response()->json([
            'message' => 'EWTR berhasil dihapus.',
        ]);
    }

    /**
     * Stamp keterangan onto EWTR PDF inside Review & Comments box in IT APPROVAL SECTION
     */
    protected function stampPdfWithKeterangan(string $sourcePath, string $outputPath, ?string $keterangan): void
    {
        $keterangan = trim($keterangan ?? '');
        if ($keterangan === '') {
            if ($sourcePath !== $outputPath) {
                copy($sourcePath, $outputPath);
            }
            return;
        }

        $scriptPath = base_path('app/Scripts/stamp_ewtr_pdf.py');
        $tempTextFile = tempnam(sys_get_temp_dir(), 'ewtr_keterangan_');
        file_put_contents($tempTextFile, $keterangan);

        $pythonBinary = 'python';
        $cmd = escapeshellcmd($pythonBinary) . ' ' .
            escapeshellarg($scriptPath) . ' --input ' .
            escapeshellarg($sourcePath) . ' --output ' .
            escapeshellarg($outputPath) . ' --text-file ' .
            escapeshellarg($tempTextFile);

        exec($cmd . ' 2>&1', $output, $returnCode);

        if (file_exists($tempTextFile)) {
            @unlink($tempTextFile);
        }

        if ($returnCode !== 0) {
            \Log::warning('Failed to stamp EWTR PDF with python script: ' . implode("\n", $output));
            if (!file_exists($outputPath) && $sourcePath !== $outputPath) {
                copy($sourcePath, $outputPath);
            }
        }
    }

    public function toggleItemStatus(Request $request, $ewtrId, $itemId)
    {
        $ewtr = Ewtr::findOrFail($ewtrId);
        $item = $ewtr->items()->where('id', $itemId)->firstOrFail();

        // If target status is explicitly given, use it; otherwise toggle
        if ($request->has('is_completed')) {
            $newStatus = (bool) $request->is_completed;
        } else {
            $newStatus = !$item->is_completed;
        }

        $item->is_completed = $newStatus;
        $item->completed_at = $newStatus ? now() : null;
        $item->completed_by = $newStatus ? auth()->id() : null;
        $item->save();

        // Recalculate EWTR status: all items must be finished to become 'sukses'
        $ewtr->syncStatus();
        $ewtr->load(['items', 'creator']);

        return response()->json([
            'message' => $newStatus ? 'Item berhasil ditandai selesai.' : 'Item dikembalikan menjadi belum selesai.',
            'item' => $item,
            'ewtr' => $ewtr,
        ]);
    }
}
