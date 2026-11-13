<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLES = ['Administrator', 'Manager', 'Cashier', 'Inventory Staff'];

    protected $fillable = ['account_code', 'name', 'username', 'email', 'password', 'role', 'status', 'avatar'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->account_code ??= 'U-'.Str::upper(Str::random(10));
        });
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return Storage::disk('public')->url($this->avatar);
        }

        return asset('images/avatar-placeholder.svg');
    }
}
