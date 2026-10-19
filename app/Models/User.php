<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


#[Fillable(['name', 'email', 'password', 'role', 'office_id', 'department_id', 'employee_id', 'job_position', 'job_level', 'join_date', 'phone_number'])]
#[Hidden(['password', 'remember_token'])]
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

    protected $appends = ['photo', 'photo_url'];

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
                $maxId = static::max('employee_id');
                // Mengambil angka terakhir, tambah 1, lalu pad dengan nol di depan (contoh: 00001)
                $nextId = $maxId ? (int)$maxId + 1 : 1;
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
