<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;

class NhanVien extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasFactory, HasApiTokens;

    protected $table = 'nhan_viens';

    protected $fillable = [
        'name',
        'phone',
        'password',
        'address',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Mutator cho cột Password (Cú pháp chuẩn của Laravel 9 / PHP 8)
     * * Tác dụng: Bất cứ khi nào Admin thêm mới hoặc sửa mật khẩu trên Voyager,
     * Laravel sẽ tự động Hash (mã hóa) mật khẩu này trước khi lưu vào Database.    
     * Hàm Hash::needsRehash giúp chống lỗi "Hash chồng Hash" khi update dữ liệu.
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Hash::needsRehash($value) ? Hash::make($value) : $value,
        );
    }

    public function scopeDesigners($query)
    {
        return $query->where('role', 1)->where('is_active', 1);
    }

    public function scopeWorkers($query)
    {
        return $query->where('role', 2)->where('is_active', 1);
    }

    public function monthlyPayrolls()
    {
        return $this->hasMany(MonthlyPayroll::class, 'nhan_vien_id');
    }
}
