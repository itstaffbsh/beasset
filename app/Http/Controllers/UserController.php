<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Services\ImageOptimizerService;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $searchTerm   = $request->get('q');
        $filterDept   = $request->get('department_id');
        $filterKantor = $request->get('office_id');
        $filterStatus = $request->get('status', 'active');

        $query = User::with(['office', 'department'])->where('id', '!=', auth()->id());

        if ($filterStatus && \Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
            $query->where('status', $filterStatus);
        }

        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('employee_id', 'like', "%{$searchTerm}%")
                  ->orWhere('job_position', 'like', "%{$searchTerm}%");
            });
        }
        if ($filterDept)   $query->where('department_id', $filterDept);
        if ($filterKantor) $query->where('office_id', $filterKantor);

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $perPage = (int) $request->get('per_page', 25);

        // Make sure column exists to avoid SQL errors
        if (in_array($sortBy, ['created_at', 'employee_id', 'name', 'email', 'role']) || \Illuminate\Support\Facades\Schema::hasColumn('users', $sortBy)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($query->paginate($perPage));
        }

        $users = $query->get();
        $departments = \App\Models\Department::all();
        $offices = \App\Models\Office::all();

        return view('users.index', compact('users', 'departments', 'offices', 'searchTerm', 'filterDept', 'filterKantor', 'filterStatus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'    => 'nullable|string|max:255|unique:users',
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users',
            'email_bsh'      => 'nullable|email',
            'personal_email' => 'nullable|email',
            'role'           => 'required|string|max:100',
            'office_id'      => 'required|exists:offices,id',
            'department_id'  => 'nullable|exists:departments,id',
            'job_position'   => 'nullable|string|max:255',
            'job_level'      => 'nullable|string|max:255',
            'join_date'      => 'nullable|date',
            'phone_number'   => 'nullable|string|max:30',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string',
            'avatar'         => 'nullable|image|max:10240',
            'password'       => 'required_if:role,admin,super_admin|nullable|min:8',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = ImageOptimizerService::convertToWebp($request->file('avatar'), 'users', 80, 1200);
        }

        $user = User::create([
            'employee_id'    => $request->employee_id,
            'name'           => $request->name,
            'email'          => $request->email,
            'email_bsh'      => $request->email_bsh,
            'personal_email' => $request->personal_email,
            'password'       => $request->password ? bcrypt($request->password) : null,
            'role'           => $request->role,
            'office_id'      => $request->office_id,
            'department_id'  => $request->department_id,
            'job_position'   => $request->job_position,
            'job_level'      => $request->job_level,
            'join_date'      => $request->join_date,
            'phone_number'   => $request->phone_number ?? $request->phone,
            'phone'          => $request->phone ?? $request->phone_number,
            'address'        => $request->address,
            'avatar'         => $avatarPath,
        ]);

        return response()->json(['message' => 'Akun karyawan berhasil dibuat.', 'data' => $user], 201);
    }

    public function update(Request $request, User $user)
    {
        $employeeIdRules = ['nullable', 'string', 'max:255'];
        if ($request->filled('employee_id') && $request->employee_id !== $user->employee_id) {
            $employeeIdRules[] = Rule::unique('users', 'employee_id')->ignore($user->id);
        }

        $request->validate([
            'employee_id'    => $employeeIdRules,
            'name'           => 'required|string|max:255',
            'email'          => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'email_bsh'      => 'nullable|email',
            'personal_email' => 'nullable|email',
            'role'           => 'required|string|max:100',
            'office_id'      => 'required|exists:offices,id',
            'department_id'  => 'nullable|exists:departments,id',
            'job_position'   => 'nullable|string|max:255',
            'job_level'      => 'nullable|string|max:255',
            'join_date'      => 'nullable|date',
            'phone_number'   => 'nullable|string|max:30',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string',
            'avatar'         => 'nullable|image|max:10240',
            'password'       => 'nullable|min:8',
        ]);

        $data = [
            'employee_id'    => $request->employee_id,
            'name'           => $request->name,
            'email'          => $request->email,
            'email_bsh'      => $request->email_bsh,
            'personal_email' => $request->personal_email,
            'role'           => $request->role,
            'office_id'      => $request->office_id,
            'department_id'  => $request->department_id,
            'job_position'   => $request->job_position,
            'job_level'      => $request->job_level,
            'join_date'      => $request->join_date,
            'phone_number'   => $request->phone_number ?? $request->phone,
            'phone'          => $request->phone ?? $request->phone_number,
            'address'        => $request->address,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = ImageOptimizerService::convertToWebp($request->file('avatar'), 'users', 80, 1200);
        }

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return response()->json(['message' => 'Data akun berhasil diperbarui.', 'data' => $user]);
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user->update([
            'password' => bcrypt($request->password)
        ]);

        return response()->json(['message' => 'Password berhasil direset.']);
    }

    public function makeAdmin(User $user)
    {
        if ($user->role !== 'admin') {
            $user->update(['role' => 'admin']);
            return response()->json(['message' => 'User berhasil dijadikan Admin.']);
        }
        return response()->json(['message' => 'User sudah menjadi Admin.'], 400);
    }

    public function details(User $user)
    {
        $activeBorrowings = \App\Models\ProductHistory::whereIn('id', function($query) {
            $query->selectRaw('MAX(id)')->from('product_histories')->groupBy('product_id');
        })->where('diterima_oleh', $user->id)
          ->where('jenis_transaksi', 'meminjam')
          ->with('product.department')
          ->get();

        $history = \App\Models\ProductHistory::where('diterima_oleh', $user->id)
            ->orWhere('pihak_pertama_id', $user->id)
            ->with(['product', 'sender', 'receiver'])
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'user' => $user,
            'active_borrowings' => $activeBorrowings,
            'history' => $history
        ]);
    }

    public function resign(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Anda tidak bisa meresign diri sendiri.'], 400);
        }

        $activeCount = \App\Models\ProductHistory::whereIn('id', function($query) {
            $query->selectRaw('MAX(id)')->from('product_histories')->groupBy('product_id');
        })->where('diterima_oleh', $user->id)
          ->where('jenis_transaksi', 'meminjam')
          ->count();

        if ($activeCount > 0) {
            return response()->json(['message' => 'Gagal Resign: Karyawan masih memegang ' . $activeCount . ' aset.'], 400);
        }

        $user->update(['status' => 'resigned']);
        return response()->json(['message' => 'Karyawan ' . $user->name . ' telah berhasil di-resign.']);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Anda tidak bisa menghapus akun Anda sendiri.'], 400);
        }

        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\UsersExport, 'data-karyawan-' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $users = User::with(['office', 'department'])->orderBy('name', 'asc')->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.users', compact('users'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('data-karyawan-' . date('Y-m-d') . '.pdf');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt',
            'duplicate_action' => 'nullable|string|in:use_existing,create_new,cancel,check',
        ]);

        $duplicateAction = $request->input('duplicate_action');

        // Jika user memilih cancel
        if ($duplicateAction === 'cancel') {
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Proses import data karyawan dibatalkan.'
            ]);
        }

        // Jika duplicate_action belum ditentukan (atau bernilai check), periksa apakah ada duplikat terlebih dahulu
        if (empty($duplicateAction) || $duplicateAction === 'check') {
            try {
                $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\UsersCheckImport, $request->file('file'));
                $rows = $sheets[0] ?? [];

                $duplicates = [];
                $seenEmails = [];

                foreach ($rows as $index => $row) {
                    $name = trim($row['full_name'] ?? $row['name'] ?? $row['nama_lengkap'] ?? $row['nama'] ?? '');
                    $emailBsh = trim($row['email_bsh'] ?? $row['email_kantor'] ?? '');
                    $personalEmail = trim($row['email'] ?? $row['email_pribadi'] ?? $row['personal_email'] ?? '');
                    $empId = isset($row['no']) ? trim((string)$row['no']) : (isset($row['employee_id']) ? trim((string)$row['employee_id']) : null);

                    if (empty($name) && empty($emailBsh) && empty($personalEmail) && empty($empId)) {
                        continue;
                    }

                    $primaryEmail = !empty($emailBsh) ? $emailBsh : (!empty($personalEmail) ? $personalEmail : null);
                    if (empty($primaryEmail)) {
                        $primaryEmail = \Illuminate\Support\Str::slug($empId ?: $name ?: uniqid(), '.') . '@balibsh.com';
                    }

                    // Cek apakah ada di database
                    $existing = User::where('email', $primaryEmail)
                        ->orWhere('email_bsh', $primaryEmail)
                        ->orWhere('personal_email', $primaryEmail)
                        ->first();

                    // Juga cek jika ada duplikat dalam file itu sendiri
                    $inCurrentFile = in_array(strtolower($primaryEmail), $seenEmails);
                    $seenEmails[] = strtolower($primaryEmail);

                    if ($existing || $inCurrentFile) {
                        $duplicates[] = [
                            'row' => $index + 2,
                            'name' => $name ?: 'Karyawan',
                            'email' => $primaryEmail,
                            'employee_id' => $empId,
                            'existing_name' => $existing ? $existing->name : 'Duplikat dalam file',
                            'existing_employee_id' => $existing ? ($existing->employee_id ?: '-') : '-',
                        ];
                    }
                }

                if (!empty($duplicates)) {
                    return response()->json([
                        'status' => 'duplicate_found',
                        'count' => count($duplicates),
                        'duplicates' => array_slice($duplicates, 0, 10),
                        'total_duplicates' => count($duplicates),
                        'message' => 'Ditemukan ' . count($duplicates) . ' data karyawan yang sudah ada atau duplikat.',
                    ], 200);
                }
            } catch (\Exception $e) {
                // Abaikan error pada pengecekan awal dan biarkan lanjut ke import
            }
        }

        // Jalankan import dengan action yang dipilih
        $action = in_array($duplicateAction, ['use_existing', 'create_new']) ? $duplicateAction : 'use_existing';

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\UsersImport($action), $request->file('file'));

            $msg = $action === 'create_new'
                ? 'Data karyawan berhasil diimpor (user duplikat ditambahkan sebagai user baru).'
                : 'Data karyawan berhasil diimpor (menggunakan & memperbarui data user yang ada).';

            return response()->json([
                'status' => 'success',
                'message' => $msg
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengimpor file: ' . $e->getMessage()], 422);
        }
    }
}
