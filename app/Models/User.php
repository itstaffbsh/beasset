<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/* |--------------------------------------------------------------------------
   | [MODEL USER / KARYAWAN]
   |--------------------------------------------------------------------------
   | Kegunaan: Sebagai representasi data orang (Admin atau Karyawan).
   */
class User extends Authenticatable
{
    /* | [ARRAY] | Daftar kolom yang boleh diisi (Mass Assignment) */
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'email_bsh',
        'personal_email',
        'password',
        'role',
        'status',
        'office_id',
        'department_id',
        'job_position',
        'job_level',
        'join_date',
        'phone_number',
        'avatar',
        'phone',
        'address',
    ];

    protected $appends = ['photo', 'photo_url', 'permissions', 'role_display_name'];

    public function roleRecord()
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    public function getPermissionsAttribute()
    {
        if ($this->role === 'super_admin') {
            return Role::getAllPermissionsList();
        }

        $role = Role::where('name', $this->role)->first();
        if ($role && is_array($role->permissions)) {
            return $role->permissions;
        }

        if ($this->role === 'admin') {
            return Role::getAllPermissionsList();
        }

        return [
            'dashboard' => ['view'],
            'products' => ['view'],
            'transfer' => ['view', 'tambah'],
            'history' => ['view'],
            'borrow-status' => ['view'],
        ];
    }

    public function getRoleDisplayNameAttribute()
    {
        if ($this->role === 'super_admin') return 'Super Admin';
        if ($this->role === 'admin') return 'Admin';
        if ($this->role === 'user') return 'User';
        
        $role = Role::where('name', $this->role)->first();
        return $role?->display_name ?? ucfirst(str_replace('_', ' ', $this->role ?? 'User'));
    }

    public function getPhotoAttribute()
    {
        return $this->avatar;
    }

    public function setPhotoAttribute($value)
    {
        $this->attributes['avatar'] = $value;
    }

    public function getPhotoUrlAttribute()
    {
        if (!$this->avatar) {
            return null;
        }
        return asset('storage/' . $this->avatar);
    }

    /* | [LOGIKA] | Menghasilkan Employee ID otomatis saat membuat user baru */
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->employee_id)) {
                $maxNum = static::whereRaw("employee_id REGEXP '^[0-9]+$'")->max(\Illuminate\Support\Facades\DB::raw('CAST(employee_id AS UNSIGNED)'));
                $nextId = $maxNum ? (int)$maxNum + 1 : 1;
                $user->employee_id = str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /* | [FUNGSI] | Relasi ke Departemen (Satu orang berada di satu departemen) */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function givenHistories()
    {
        return $this->hasMany(ProductHistory::class, 'dioper_oleh');
    }

    public function receivedHistories()
    {
        return $this->hasMany(ProductHistory::class, 'diterima_oleh');
    }

    /* | [FUNGSI] | Relasi ke Kantor (Satu orang bekerja di satu kantor) */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, \Laravel\Sanctum\HasApiTokens, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
