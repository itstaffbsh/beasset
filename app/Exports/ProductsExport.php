<?php

namespace App\Exports;

use App\Models\Aset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Aset::with(['classification', 'department', 'histories.receiver'])->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Asset Category',
            'ID Barang',
            'Lend Date',
            'Returned Date',
            'Screen ID',
            'Description',
            'Proccessor',
            'NEW RAM',
            'RAM',
            'New SSD',
            'SSD',
            'OS',
            'Device Name',
            'Serial Number',
            'IMEI',
            'Remaks',
            'MAC ADDRESS',
        ];
    }

    public function map($product): array
    {
        $lastHistory = $product->histories->first();
        
        $lendDate = '';
        if ($lastHistory && $lastHistory->lend_date) {
            try {
                $lendDate = Carbon::parse($lastHistory->lend_date)->format('Y-m-d');
            } catch (\Exception $e) {
                $lendDate = (string)$lastHistory->lend_date;
            }
        }

        $returnedDate = '';
        if ($lastHistory && $lastHistory->returned_date) {
            try {
                $returnedDate = Carbon::parse($lastHistory->returned_date)->format('Y-m-d');
            } catch (\Exception $e) {
                $returnedDate = (string)$lastHistory->returned_date;
            }
        }
        
        return [
            $lastHistory->receiver->name ?? '',
            $product->classification?->nama_klasifikasi ?? '',
            $product->full_nomor_unik,
            $lendDate,
            $returnedDate,
            $product->screen_id ?? '',
            $product->description ?? '',
            $product->processor ?? '',
            $product->new_ram ?? '',
            $product->ram ?? '',
            $product->new_ssd ?? '',
            $product->ssd ?? '',
            $product->os ?? '',
            $product->device_name ?? '',
            $product->serial_number ?? '',
            $product->imei ?? '',
            $product->remarks ?? ($lastHistory->remarks ?? ''),
            $product->mac_address ?? '',
        ];
    }
}
