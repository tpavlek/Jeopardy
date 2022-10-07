<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guarded = [];

    public static function forName(string $name): self
    {
        return self::query()
            ->firstOrCreate([ 'name' => $name ], [ 'is_admin' => false ]);
    }

    public static function current(): ?self
    {
        return Auth::user();
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
}
