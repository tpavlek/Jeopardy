<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, HasFactory, Notifiable, Authenticatable, Authorizable;

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
