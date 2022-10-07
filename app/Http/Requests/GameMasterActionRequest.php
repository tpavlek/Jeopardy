<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

abstract class GameMasterActionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return User::current()?->isAdmin();
    }

}
