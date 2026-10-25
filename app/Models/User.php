<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Các trường được phép lưu khi tạo tài khoản.
     */
    protected $fillable = [
        'name',
        'username',
        'nim',
        'email',
        'password',
        'role',
    ];

    /**
     * Ẩn các trường nhạy cảm.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Tự động băm mật khẩu khi lưu.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Quan hệ giữa người dùng và đơn hàng.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
