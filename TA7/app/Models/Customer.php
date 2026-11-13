<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Customer extends Model
{
    protected $fillable = ['account_code', 'name', 'email', 'phone', 'address', 'status'];

    protected static function booted(): void
    {
        static::creating(function (Customer $customer): void {
            $customer->account_code ??= 'C-'.Str::upper(Str::random(10));
        });
    }
}
