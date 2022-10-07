<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** @property GameCategory pivot */
class Category extends Model
{
    use HasFactory;

    public static function build(string $name): self
    {
        return self::query()->create([ 'name' => $name ]);
    }

    public static function findNamed(string $name): self
    {
        return self::query()->where('name', $name)->firstOrFail();
    }
}
