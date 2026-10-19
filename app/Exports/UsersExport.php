<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return User::with(['office', 'department'])->orderBy('name', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Full Name',
            'Email BSH',
            'Email',
            'Organization',
            'Job Position',
            'Job Level',
            'Join Date',
            'Nomor HP',
            'Area',
            'Alamat',
            'Role',
        ];
    }

    public function map($user): array
    {
        return [
            $user->employee_id ?: '-',
            $user->name,
            $user->email_bsh ?: $user->email,
            $user->personal_email ?: '-',
            $user->department->nama_departemen ?? '-',
            $user->job_position ?: '-',
            $user->job_level ?: '-',
            $user->join_date ? date('Y-m-d', strtotime($user->join_date)) : '-',
            $user->phone ?: ($user->phone_number ?: '-'),
            $user->office->nama_kantor ?? '-',
            $user->address ?: '-',
            $user->role,
        ];
    }
}
